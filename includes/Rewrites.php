<?php

// ABSPATH prevent public user to directly access your .php files through URL.
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class Rewrites
 *
 */
class Rewrites
{
    public function create_rewrite_rules($rules): array
    {
        global $wp_rewrite;
        $newRule  = ['auth/(.+)' => 'index.php?auth=' . $wp_rewrite->preg_index(1)];
        $newRules = $newRule + $rules;

        return $newRules;
    }

    public function add_query_vars($qvars): array
    {
        $qvars[] = 'auth';
        return $qvars;
    }

    public function flush_rewrite_rules()
    {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }

    public function template_redirect_intercept(): void
    {
        global $wp_query;
        // casdoor will add another ? to the uri, this will make the value of auth like this : casdoor?code=c9550137370a99bc2137
        $matches = [];
        preg_match('/^([a-zA-Z]+)(\?code=[a-zA-Z0-9]+)?$/', $wp_query->get('auth'), $matches);
        if (count($matches) == 3 && $matches[1] == 'casdoor') {
            $tmp = explode('=', $matches[2]);
            if ($tmp[0] == '?code') {
                $url =  home_url("?auth=casdoor&code={$tmp[1]}");
                wp_redirect($url);
            }
        }

        if ($wp_query->get('auth') && $wp_query->get('auth') == 'casdoor') {
            require_once(dirname(dirname(__FILE__)) . '/includes/callback.php');
            exit;
        }
    }
}

$rewrites = new Rewrites();
add_filter('rewrite_rules_array', [$rewrites, 'create_rewrite_rules']);
add_filter('query_vars', [$rewrites, 'add_query_vars']);
add_filter('wp_loaded', [$rewrites, 'flush_rewrite_rules']);
add_action('template_redirect', [$rewrites, 'template_redirect_intercept']);
