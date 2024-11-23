<?php
// If we were not told to use a specific instance of FormHelper
if (!isset($Form) || !$Form) {
    // ... use generic FormHelper
    $Form = $this->Form;
}
echo $Form->control('account', ['options' => $accounts]);