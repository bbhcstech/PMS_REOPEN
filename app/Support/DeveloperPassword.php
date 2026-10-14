<?php

namespace App\Support;

class DeveloperPassword
{
    public static function generate(): string
    {
        $groups = ['ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz', '0123456789', '!@#$%&*?'];
        $characters = [];
        foreach ($groups as $group) $characters[] = $group[random_int(0, strlen($group) - 1)];
        $all = implode('', $groups);
        while (count($characters) < 10) $characters[] = $all[random_int(0, strlen($all) - 1)];
        for ($i = count($characters) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$characters[$i], $characters[$j]] = [$characters[$j], $characters[$i]];
        }
        return implode('', $characters);
    }
}
