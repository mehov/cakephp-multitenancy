<?php
echo $this->Form->create($entity).PHP_EOL;
echo $this->Form->control('name').PHP_EOL;
echo $this->Form->control('is_active').PHP_EOL;
echo $this->Form->button('Save').PHP_EOL;
echo $this->Form->end().PHP_EOL;
