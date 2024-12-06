<?php
$account = \Multitenancy\Account::get();
if ($account) {
    echo $this->Html->link($account->name, ['_name' => \Multitenancy\Plugin::getPlugin().':ChooseAccount']);
} else {
    echo $this->Html->link('Choose Account', ['_name' => \Multitenancy\Plugin::getPlugin().':ChooseAccount']);
}