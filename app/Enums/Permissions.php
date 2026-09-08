<?php

namespace App\Enums;

enum Permissions: string
{
    // Asset Management
    case VIEW_ASSETS = 'view assets';
    case CREATE_ASSETS = 'create assets';
    case EDIT_ASSETS = 'edit assets';
    case DELETE_ASSETS = 'delete assets';
    case MANAGE_ASSETS = 'manage assets';

    // Asset
    case VIEW_CATEGORIES = 'view categories';
    case MANAGE_CATEGORIES = 'manage categories';
    case VIEW_ASSET_TYPES = 'view asset types';
    case MANAGE_ASSET_TYPES = 'manage asset types';
    case VIEW_ASSET_HISTORY = 'view asset history';
    case MANAGE_ASSET_DOCUMENTS = 'manage asset documents';

    // Organization Management
    case VIEW_ORGANIZATIONS = 'view organizations';
    case MANAGE_ORGANIZATIONS = 'manage organizations';

    // Department Management
    case VIEW_DEPARTMENTS = 'view departments';
    case MANAGE_DEPARTMENTS = 'manage departments';

    // Employee Management
    case VIEW_EMPLOYEES = 'view employees';
    case MANAGE_EMPLOYEES = 'manage employees';

    // Reports & Analytics
    case VIEW_REPORTS = 'view reports';
    case GENERATE_REPORTS = 'generate reports';
    case VIEW_DASHBOARD = 'view dashboard';

    // User Management
    case VIEW_USERS = 'view users';
    case MANAGE_USERS = 'manage users';

    // Role & Permission Management
    case VIEW_ROLES = 'view roles';
    case MANAGE_ROLES = 'manage roles';
    case VIEW_PERMISSIONS = 'view permissions';
    case MANAGE_PERMISSIONS = 'manage permissions';

    // Occupation Management
    case VIEW_OCCUPATIONS = 'view occupations';
    case MANAGE_OCCUPATIONS = 'manage occupations';
    case DELETE_OCCUPATIONS = 'delete occupations';

    // System Administration
    case SYSTEM_SETTINGS = 'system settings';
    case AUDIT_LOGS = 'audit logs';

    public function getLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}