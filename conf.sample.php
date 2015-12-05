<?php

$conf = [
        // This is the GitHub's username which will post issues
        'username'              => 'your_username',

        // GitHub password of the given account
        'password'              => 'your_password',

        // If no repository is defined through the $_GET['project'] parameter, this one is get as default
        'default_project'       => 'username/repository',

        // Uncomment this row if you want to permit publishing of new issues only on the specified repository, overwriting any other request
        // 'fixed_project'      => 'username/repository'
];
