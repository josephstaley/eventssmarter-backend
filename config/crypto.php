<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */
    'abi' => '[{"inputs":[],"stateMutability":"nonpayable","type":"constructor"},{"inputs":[{"internalType":"uint256","name":"_price","type":"uint256"},{"internalType":"uint256","name":"_appFeePercentage","type":"uint256"},{"internalType":"address payable","name":"_seller","type":"address"},{"internalType":"uint256[]","name":"_voucher_ids","type":"uint256[]"}],"name":"buyItem","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"payable","type":"function"},{"inputs":[{"internalType":"uint256[]","name":"_voucher_ids","type":"uint256[]"}],"name":"contains","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"owner","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"_newOwner","type":"address"}],"name":"setOwnerShip","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"withdraw","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"}]',
    
    'smartcontract_address' => '0xF26972aB7B7538bC37a6604f2D99eeEDCc0882Ba',

    'network' => 'main',  // main

    'host' => 'https://mainnet.infura.io/v3/9aa3d95b3bc440fa88ea12eaa4456161',
];