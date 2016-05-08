<h3><img src="<?= $this->url->dir() ?>plugins/Sendgrid/sendgrid-icon.png"/>&nbsp;Sendgrid</h3>
<div class="listing">
    <input type="text" class="auto-select" readonly="readonly" value="<?= $this->url->href('webhook', 'receiver', array('plugin' => 'sendgrid', 'token' => $values['webhook_token']), false, '', true) ?>"/><br/>
    <p class="form-help"><a href="https://kanboard.net/plugin/sendgrid" target="_blank"><?= t('Help on Sendgrid integration') ?></a></p>
</div>
