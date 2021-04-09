<?php

/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = 'My Yii Application';
?>
<div class="site-index">

    <div class="body-content">

        <div class="row">
            <div class="col-lg-4">
                <h2>Enter link for user list</h2>
                e.g. &laquo;https://raw.githubusercontent.com/fredeom/i2crm_test/master/users.txt&raquo;
                <p>
                  <?= Html::beginForm() ?>
                  <?= Html::input('text', 'userListLink', '', ['placeholder' => "", 'style' => 'margin-top: 5px;']) ?>
                  <?= Html::input('hidden', 'userList', $userList); ?>
                  <?= Html::submitButton('Add', ['class' => 'btn btn-primary']); ?>
                  <?= Html::endForm(); ?>
                </p>

                <h2>Edit current user list</h2>

                <p>
                  <?= Html::beginForm() ?>
                  <?= Html::textArea('userList', $userList, ['style' => 'min-width: 300px; min-height: 200px;']) ?> <br>
                  <?= Html::submitButton('Save', ['class' => 'btn btn-primary']); ?> Save and click <i>Refresh Table</i> button.
                  <?= Html::endForm(); ?>
                </p>
            </div>
            <div class="col-lg-8">
              <p style="margin-top:1.5em;">
                <?= Html::beginForm() ?>
                <?= Html::input('hidden', 'refresh', 1); ?>
                <?= Html::input('hidden', 'userList', $userList); ?>
                <?= Html::submitButton('Refresh Table', ['class' => 'btn btn-primary']); ?> After click automatic refresh happens every 20 minutes
                <?= Html::endForm(); ?>
                <script>
                  const refresh = '<?= $refresh ?>';
                  if (refresh.length > 0) setTimeout(() => document.forms[2].submit(), 10 * 60 * 1000);
                  //const data = <?php echo json_encode($repos, JSON_HEX_TAG); ?>;
                  //console.log(data);
                </script>
              </p>
              <p>
                <?php
                  if ($token) {
                    echo 'Now you authorized and github api limits are higher';
                  } else {
                ?>
                <?= Html::beginForm() ?>
                <?= Html::input('hidden', 'authorize', 1); ?>
                <?= Html::submitButton('Authorize', ['class' => 'btn btn-danger']); ?> Authorize to overcome github api limits
                <?= Html::endForm(); ?>
                <?php
                  }
                ?>
              </p>
              <p>
                <table border="1" style="width:100%; table-layout: fixed;">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Updated_at</th>
                      <th>Link to repo</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                      foreach ($repos as $repo) {
                        echo '<tr>
                                <td>' . $repo['name']. '</td>
                                <td>' . $repo['updated_at']. '</td>
                                <td><a href="' . $repo['html_url'] . '">' . $repo['html_url'] . '</a></td>
                              </tr>';
                      }
                    ?>
                  </tbody>
                </table>
              </p>
            </div>
        </div>

    </div>
</div>
