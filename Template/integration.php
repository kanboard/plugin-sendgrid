<h3><img src="<?= $this->url->dir() ?>plugins/Sendgrid/sendgrid-icon.png"/>&nbsp;Sendgrid</h3>
<div class="listing">
    <input type="text" class="auto-select" readonly="readonly" value="<?= $this->url->href('WebhookController', 'receiver', array('plugin' => 'sendgrid', 'token' => $values['webhook_token']), false, '', true) ?>">

    <?= $this->form->label(t('Sendgrid API user'), 'sendgrid_api_user') ?>
    <?= $this->form->text('sendgrid_api_user', $values) ?>

    <?= $this->form->label(t('Sendgrid API key'), 'sendgrid_api_key') ?>
    <?= $this->form->password('sendgrid_api_key', $values) ?>

    <p class="form-help"><a href="https://kanboard.net/plugin/sendgrid" target="_blank"><?= t('Help on Sendgrid integration') ?></a></p>

    <div class="form-actions">
        <input type="submit" value="<?= t('Save') ?>" class="btn btn-blue">
    </div>
</div>
