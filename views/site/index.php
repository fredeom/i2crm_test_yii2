<?php

/* @var $this yii\web\View */
/* @var $userList string */
/* @var $refresh int */
/* @var $repos array */
/* @var $token string */

use yii\helpers\Html;

$this->title = 'My Yii Application';
?>
<div class="site-index">

    <div class="body-content">

        <div class="row">
            <div class="col-lg-4" style="overflow: hidden; white-space: nowrap; text-overflow: ellipsis">
                <h2>Enter link for user list</h2>
                e.g. <a href="#" class="copy-to-clipboard-by-click" data-target="#samplelink">copy following</a>: &laquo;<span id="samplelink">https://raw.githubusercontent.com/fredeom/i2crm_test/master/users.txt</span>&raquo;
                <p>
                  <?= Html::beginForm() ?>
                  <?= Html::input('text', 'userListLink', '', ['style' => 'margin-top: 5px;']) ?>
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
              <p style="margin-top: 1.5em;">
                <?= Html::beginForm() ?>
                <?= Html::input('hidden', 'refresh', 1); ?>
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
                <?php if ($token): ?>
                    Now you authorized and github api limits are higher
                <?php else: ?>
                    <?= Html::beginForm() ?>
                    <?= Html::input('hidden', 'authorize', 1); ?>
                    <?= Html::submitButton('Authorize', ['class' => 'btn btn-danger']); ?> Authorize to overcome github api limits
                    <?= Html::endForm(); ?>
                <?php endif; ?>
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
                    <?php foreach ($repos as $repo): ?>
                        <tr>
                            <td><?= Html::encode($repo['name']) ?></td>
                            <td><?= Html::encode($repo['updated_at']) ?></td>
                            <td><a href="<?= Html::encode($repo['html_url']) ?>"><?= Html::encode($repo['html_url']) ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </p>
            </div>
        </div>

    </div>
</div>
