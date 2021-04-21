<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller {
    public function actionIndex() {
      if (Yii::$app->request->post('authorize')) {
        return $this->redirect('https://github.com/login/oauth/authorize?client_id=' . Yii::$app->params['client_id']);
      }
      $githubOAthCode = Yii::$app->request->get('code');
      if ($githubOAthCode) {
        $sURL = 'https://github.com/login/oauth/access_token';
        $sPD = 'client_id=' . Yii::$app->params['client_id'] . '&client_secret=' . Yii::$app->params['client_secret'] . '&code=' . $githubOAthCode;
        $aHTTP = [
          'http' => [
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => $sPD
          ]
        ];
        $context = stream_context_create($aHTTP);
        $tokenString = file_get_contents($sURL, false, $context);
        $tokenStringArr = explode('&', $tokenString);
        $tokenArr = explode('=', $tokenStringArr[0]);
        $token = $tokenArr[1];
        return $this->redirect('/?token=' . $token);
      }
      $token = Yii::$app->request->get('token');

      $userListStr = Yii::$app->request->post('userList');
      $userList = explode(' ', '' . $userListStr);

      $userListLink = Yii::$app->request->post('userListLink');
      $userListFromLink = @file_get_contents($userListLink);
      $userListFromLink = !$userListFromLink ? [] : explode(' ', $userListFromLink);
      $users = array_unique(array_merge($userList, $userListFromLink));

      $userListStr = trim(implode(' ', $users));

      $refresh = Yii::$app->request->post('refresh');
      $repos = [];
      if ($refresh && !Yii::$app->cache->get($userListStr)) {
        $nodes = [];
        $results = [];
        foreach ($userList as $user) {
          if ($user) {
            $nodes[] = 'https://api.github.com/users/' . $user . '/repos';
          }
        }
        $node_count = count($nodes);
        $curl_arr = array();
        $master = curl_multi_init();
        for($i = 0; $i < $node_count; $i++) {
            $url =$nodes[$i];
            $curl_arr[$i] = curl_init($url);
            curl_setopt($curl_arr[$i], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl_arr[$i], CURLOPT_USERAGENT, 'curl');
            if ($token) {
              curl_setopt($curl_arr[$i], CURLOPT_HTTPHEADER, [
                  'Authorization: token ' . Yii::$app->request->get('token'),
              ]);
            }
            curl_multi_add_handle($master, $curl_arr[$i]);
        }

        for (;;) {
          curl_multi_exec($master,$running);
          if ($running < 1) break;
          curl_multi_select($master, 1);
        }

        for($i = 0; $i < $node_count; $i++) {
            $results[] = curl_multi_getcontent  ( $curl_arr[$i]  );
        }
        foreach ($results as $result) {
          if ($result[0] == '[') {
            $accRepos = json_decode($result);
            foreach ($accRepos as $repo) {
              $repos[] = [ 'html_url' => $repo->html_url, 'name' => $repo->name, 'updated_at' => $repo->updated_at];
            }
          }
        }
        usort($repos, fn($a, $b) => strcmp($b['updated_at'], $a['updated_at']));
        $repos = array_slice($repos, 0, 10);
        Yii::$app->cache->set($userListStr, $repos, 10 * 60);
      } else {
        $repos = Yii::$app->cache->get($userListStr);
        if (!$repos) $repos = [];
      }
      return $this->render('index', [
          'userList' => $userListStr,
          'repos' => $repos,
          'token' => $token,
          'refresh' => $refresh
        ]
      );
    }
}
