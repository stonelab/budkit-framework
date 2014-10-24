<?php

namespace Budkit\View\Layout;

interface Parser {

    public function execute($content, $data = []);

}