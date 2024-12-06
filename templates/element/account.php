<?php
$account = \Bakeoff\Multitenancy\Account::get();
if ($account) {
    echo $this->Html->link($account->name, ['_name' => \Bakeoff\Multitenancy\Plugin::getPlugin().':ChooseAccount']);
} else {
    echo $this->Html->link('Choose Account', ['_name' => \Bakeoff\Multitenancy\Plugin::getPlugin().':ChooseAccount']);
}