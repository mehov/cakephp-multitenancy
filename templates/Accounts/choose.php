<?php if ($this->Account->getAccount()): ?>
<p>Currently selected: <?= $this->Account->getAccount()->name ?></p>
<?php endif; ?>
<table>
    <tr>
        <th>Name</th>
        <th>Created</th>
        <th>Last accessed</th>
    </tr>
<?php foreach ($accounts as $account): ?>
    <tr>
        <td>
            <a href="<?= \Cake\Routing\Router::url([$account->id]) ?>">
                <?= $account->name ?>
            </a>
        </td>
        <td><?= $account->created ?></td>
        <td><?= $account->accessed ?></td>
    </tr>
<?php endforeach; ?>
</table>
