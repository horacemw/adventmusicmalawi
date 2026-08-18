<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Users
            'manage_users', 'view_users',
            // Content
            'manage_songs', 'manage_albums', 'manage_artists', 'manage_groups',
            'manage_churches', 'manage_hymn_books', 'manage_events',
            // Taxonomies
            'manage_taxonomy',
            // Submissions & payments
            'moderate_submissions', 'view_payments', 'manage_payments',
            // Copyright
            'manage_copyright',
            // Promotions & ads
            'manage_promotions', 'manage_advertising',
            // System
            'manage_settings', 'view_analytics', 'view_audit_log',
            // Artist-facing
            'submit_music', 'manage_own_artist_profile', 'manage_own_group_profile',
            'view_own_analytics', 'promote_own_content',
            // Advertiser-facing
            'purchase_advertising',
            // Listener
            'stream_music', 'create_playlist', 'like_content', 'follow_content',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $roles = [
            'super-admin' => $permissions,
            'admin' => array_diff($permissions, ['manage_settings']),
            'music-moderator' => [
                'moderate_submissions', 'manage_songs', 'manage_albums',
                'manage_copyright', 'view_audit_log', 'view_analytics',
                'stream_music', 'create_playlist', 'like_content', 'follow_content',
            ],
            'artist' => [
                'submit_music', 'manage_own_artist_profile', 'view_own_analytics',
                'promote_own_content', 'stream_music', 'create_playlist',
                'like_content', 'follow_content',
            ],
            'group-manager' => [
                'submit_music', 'manage_own_group_profile', 'view_own_analytics',
                'promote_own_content', 'stream_music', 'create_playlist',
                'like_content', 'follow_content',
            ],
            'advertiser' => [
                'purchase_advertising', 'view_own_analytics',
                'stream_music', 'create_playlist', 'like_content', 'follow_content',
            ],
            'listener' => [
                'stream_music', 'create_playlist', 'like_content', 'follow_content',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($rolePermissions);
        }
    }
}
