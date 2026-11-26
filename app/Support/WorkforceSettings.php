<?php

namespace App\Support;

/** Keep shared workforce screens inside the workspace that opened them. */
final class WorkforceSettings
{
    public static function active(): bool
    {
        return request()->routeIs(
            'admin.settings.departments.*',
            'admin.settings.parent-departments.*',
            'admin.settings.designations.*'
        );
    }

    public static function name(string $name): string
    {
        return self::active() && preg_match('/^(departments|parent-departments|designations)\./', $name)
            ? 'admin.settings.'.$name
            : $name;
    }

    public static function route(string $name, $parameters = [], bool $absolute = true): string
    {
        return route(self::name($name), $parameters, $absolute);
    }
}
