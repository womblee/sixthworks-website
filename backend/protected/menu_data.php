<?php

$MENU_DATA =
[
    // A bit too old, thought it was going to be in one array only.
    "games" =>
    [
        "gta" =>
        [
            "full_name" => "Grand Theft Auto V",
            "last_update" => "1651586471",
            "versions" =>
            [
                "dev",
                "beta",
                "full",
            ],
            "file_info" =>
            [
                "process" => "GTA5.exe",
                "name" => "release_gta.dll",
                "type" => "dll",
            ],

            "cost" => "20$",
        ]
    ],
];

$CRC_DATA =
[
    "launcher" =>
    [
        "TRPC" => 0x4BF,
        "CVR" => 0x27DF,
        "CDR" => 0x27DF,
        "GA" => 0x68BF,
        "ISFN" => 0x4BF,
        "IC" => 0x7E5F,
        "CSTR" => 0x7E5F,
        "CHE" => 0x68BF,
        "WB" => 0x27DF,
        "GG" => 0x909F
    ]
];

$VALIDATION_DATA =
[
    "launcher" => "0.0.1"
];

$VERSION_DATA =
[
    "dev" =>
    [
        "full_name" => "Developer"
    ],
    "beta" =>
    [
        "full_name" => "Beta"
    ],
    "full" =>
    [
        "full_name" => "Premium"
    ]
];

$CARD_DATA =
[
    "gta" =>
    [
        "title" => "Grand Theft Auto V",
        "content" => "<p><strong>Everything that you could possibly need for modding...</strong></p><div style='padding-left: 25px'><div class='row'><div class='col-6'><ul style='padding-left: unset;'><li style='font-size: 20px' class='mb-2'>Future updates are free</li><li style='font-size: 20px' class='mb-2'>Modern user interface</li><li style='font-size: 20px' class='mb-2'>Powerful protections</li><li style='font-size: 20px' class='mb-2'>Two modes of user interface</li></ul></div><div class='col-6'><ul style='padding-left: unset;'><li style='font-size: 20px' class='mb-2'>Future updates are free</li><li style='font-size: 20px' class='mb-2'>Modern user interface</li><li style='font-size: 20px' class='mb-2'>Powerful protections</li><li style='font-size: 20px' class='mb-2'>Two modes of user interface</li></ul></div></div></div>",
        "img" => "sixthworks_list.png",
    ]
];

$RESELLERS_DATA =
[
    "gta" =>
    [
        "ezMod" =>
        [
            "methods" =>
            [
                "PayPal" =>
                [
                    "price" => "",
                    "url" => "https://nigger.lol",
                ],
                "Skrill" =>
                [
                    "price" => "20$",
                    "url" => "https://nigger.lol",
                ]
            ],
        ]
    ]
];

$CHANGELOG_DATA =
[
    "gta" =>
    [
        1651586471 =>
        [
            "Added Directonal (W-A-S-D) auto strafe mode",
            "Added Minigun Tap Fire Aimbot option",
            "Added matchmaking region selector",
            "Fixed cases where config rename button woudn't work",
            "Fixed crashing after today's game update",
            "Changed maximum Double Tap / Warp charge to 24 ticks",
            "Changed EstimateAbsVelocityFn signature for better compatibility with other software",
            "Fixed a case where playerlist priority could be set on local player",
            "Added Auto Ready (F4) for MvM gamemode",
            "Added options to copy and paste configs from clipboard, allowing easy sharing of your configs with friends",
            "Added 'ignore thirdperson' and 'ignore friends' options to 'Disable Aimbot when Spectated' feature",
            "Added custom color for backtrack indicator",
            "Added esp class option mode 'icon' which draw icon above player",
            "Added testing version of items respawn timer (note the esp position is inaccurate rn, will fix later)",
            "Fixed 'wait for charge' option to work against disguised players, players marked with 'baim' "
        ]
    ]
];

?>