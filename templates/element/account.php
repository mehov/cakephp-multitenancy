<?php
$account = \Multitenancy\Account::get();
if ($account) {
    echo $account->name;
} else {
    echo $this->Html->link('Choose Account', ['_name' => 'Multitenancy:ChooseAccount']);
}