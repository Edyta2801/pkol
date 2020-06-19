<?php

$competition = ['2972'];
update_post_meta(315, 'olimpic_information_0_olimpic', $competition);
update_post_meta(315, '_olimpic_information_0_olimpic', 'field_5ece3924db809');
add_post_meta(315, 'olimpic_information', 1);
add_post_meta(315, '_olimpic_information', 'field_5ece346638855');
$meta = get_post_meta(315, "olimpic_information_0_olimpic");
var_dump($meta);
