<?php

namespace app\controllers;

use Yii;
use yii\httpclient\Client;
use yii\web\Controller;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller {

    private function redirectAfterGithubOAuthorization($githubOAthCode) {
        $sURL = Yii::$app->params['github_api_oauth_access_token_url'];
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl($sURL)
            ->setData([
                'client_id' => Yii::$app->params['github_api_client_id'],
                'client_secret' => Yii::$app->params['github_api_client_secret'],
                'code' => $githubOAthCode])
            ->send();
        if ($response->isOk) {
            if (isset($response->data['error'])) {
                Yii::$app->session->addFlash('error', 'Cant OAuthorize with github. ' . $response->data['error_description'] . ' See <a href="' . $response->data['error_uri']. '">this</a>.');
            } else {
                Yii::$app->session->addFlash('success', 'Github OAuthtorization succeeded');
                return $this->redirect(['/', 'token' => $response->data['access_token']]);
            }
        } else {
            Yii::$app->session->addFlash('error', 'Cant connect to github server');
        }
        return $this->redirect('/');
    }

    private function getUserListFromLink($userListLink) {
        $userListFromLink = [];
        if (!empty($userListLink)) {
            $client = new Client();
            $response = $client->createRequest()
                ->setMethod('GET')
                ->setUrl($userListLink)
                ->send();
            if ($response->isOk) {
                $userListFromLink = $response->getContent();
                $userListFromLink = empty($userListFromLink) ? [] : explode(' ', $userListFromLink);
                Yii::$app->session->addFlash('success', 'Repos from <a href="' . $userListLink . '">' . $userListLink . '</a> added');
                return $userListFromLink;
            } else {
                Yii::$app->session->addFlash('error', 'Failed to connect to ' . $userListLink);
            }
        } else {
            Yii::$app->session->addFlash('error', 'Empty url specified');
            return [];
        }
    }

    private function downloadReposInfo($userListStr, $token)
    {
        $users = explode(' ', $userListStr);
        foreach ($users as $user) {
            if (empty(Yii::$app->cache->get($user))) {
                Yii::$app->async->run(function() use ($user, $token) {
                    $client = new Client([
                        'responseConfig' => [
                            'format' => Client::FORMAT_JSON
                        ],
                    ]);
                    $request = $client->createRequest()
                        ->setMethod('GET')
                        ->addHeaders(['user-agent' => 'Yii http client'])
                        ->setUrl(Yii::$app->params['github_api_users_url'] . $user . '/repos');
                    if (!empty($token)) {
                        $request->getHeaders()->add('Authorization', 'token ' . $token . '123');
                    }
                    $response = $request->send();
                    if ($response->isOk) {
                        $result = $response->data;
                        Yii::$app->cache->set($user, $result);
                    }
                });
            }
        }
        Yii::$app->async->wait();
    }

    public function getFirst10CachedRepos($userListStr)
    {
        $users = explode(' ', $userListStr);
        $info = [];
        foreach ($users as $user) {
            $repos = Yii::$app->cache->get($user);
            if (!empty($repos)) {
                foreach ($repos as $repo) {
                    $info[] = [ 'html_url' => $repo['html_url'], 'name' => $repo['name'], 'updated_at' => $repo['updated_at']];
                }
            }
        }
        usort($info, fn($a, $b) => strcmp($b['updated_at'], $a['updated_at']));
        $info = array_slice($info, 0, 10);
        return $info;
    }

    public function actionIndex() {
        if (Yii::$app->request->post('authorize')) {
            return $this->redirect(Yii::$app->params['github_api_oauth_authorize_url'] . '?client_id=' . Yii::$app->params['github_api_client_id']);
        }
        $githubOAthCode = Yii::$app->request->get('code');
        if ($githubOAthCode) {
            return $this->redirectAfterGithubOAuthorization($githubOAthCode);
        }

        $userListLink = Yii::$app->request->post('userListLink') ?? '';
        $userList = Yii::$app->request->post('userList') ?? '';

        $userListFromLink = empty($userListLink) ? [] : $this->getUserListFromLink($userListLink);

        if (empty($userList)) {
            $userList = Yii::$app->cache->get('userList') ?? '';
        } else {
            Yii::$app->cache->set('userList', $userList);
        }

        $userListFromForm = explode(' ', $userList);
        $userListStr = trim(implode(' ', array_unique(array_filter(array_merge($userListFromForm, $userListFromLink)))));

        Yii::$app->cache->set('userList', $userListStr);

        $refresh = Yii::$app->request->post('refresh');

        $token = Yii::$app->request->get('token');

        if ($refresh) {
            $this->downloadReposInfo($userListStr, $token);
        }

        return $this->render('index', [
            'userList' => $userListStr,
            'repos' => $this->getFirst10CachedRepos($userListStr),
            'token' => $token,
            'refresh' => $refresh
        ]);
    }

    public function actionTest()
    {
        Yii::$app->async->run(function() {
           return 123;
        });

        $some = Yii::$app->async->wait();

        return $some[0];
    }
}
