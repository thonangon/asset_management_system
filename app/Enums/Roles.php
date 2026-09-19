<?php

namespace App\Enums;

enum Roles: string
{
    case SUPER_ADMIN = 'super_admin';
    case IT_MANAGER = 'it_manager';
    case FINANCE_MANAGER = 'finance_manager';
    case HR_MANAGER = 'hr_manager';
    case RISK_MANAGER = 'risk_manager';
    case AUDIT_MANAGER = 'audit_manager';
    case EMPLOYEE = 'employee';

    public function getLabel(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::IT_MANAGER => 'IT Manager',
            self::FINANCE_MANAGER => 'Finance Manager',
            self::HR_MANAGER => 'HR Manager',
            self::RISK_MANAGER => 'Risk Manager',
            self::AUDIT_MANAGER => 'Audit Manager',
            self::EMPLOYEE => 'Employee',
        };
    }

    public function getPermissions(): array
    {
        return match ($this) {
            self::SUPER_ADMIN => Permissions::cases(),
            self::IT_MANAGER => [
                Permissions::VIEW_ASSETS,
                Permissions::CREATE_ASSETS,
                Permissions::EDIT_ASSETS,
                Permissions::DELETE_ASSETS,
                Permissions::MANAGE_ASSETS,
                Permissions::VIEW_CATEGORIES,
                Permissions::MANAGE_CATEGORIES,
                Permissions::VIEW_ASSET_TYPES,
                Permissions::MANAGE_ASSET_TYPES,
                Permissions::VIEW_ASSET_HISTORY,
                Permissions::MANAGE_ASSET_DOCUMENTS,
                Permissions::VIEW_LOCATIONS,
                Permissions::MANAGE_LOCATIONS,
                Permissions::VIEW_REPORTS,
                Permissions::VIEW_DASHBOARD,
                Permissions::VIEW_USERS,
                Permissions::MANAGE_USERS,
                Permissions::SYSTEM_SETTINGS,
            ],
            self::FINANCE_MANAGER => [
                Permissions::VIEW_ASSETS,
                Permissions::VIEW_CATEGORIES,
                Permissions::VIEW_ASSET_TYPES,
                Permissions::VIEW_ASSET_HISTORY,
                Permissions::VIEW_REPORTS,
                Permissions::GENERATE_REPORTS,
                Permissions::VIEW_DASHBOARD,
            ],
            self::HR_MANAGER => [
                Permissions::VIEW_DEPARTMENTS,
                Permissions::MANAGE_DEPARTMENTS,
                Permissions::VIEW_EMPLOYEES,
                Permissions::MANAGE_EMPLOYEES,
                Permissions::VIEW_DASHBOARD,
            ],
            self::RISK_MANAGER => [
                Permissions::VIEW_ASSETS,
                Permissions::VIEW_CATEGORIES,
                Permissions::VIEW_ASSET_TYPES,
                Permissions::VIEW_ASSET_HISTORY,
                Permissions::VIEW_REPORTS,
                Permissions::VIEW_DASHBOARD,
                Permissions::AUDIT_LOGS,
            ],
            self::AUDIT_MANAGER => [
                Permissions::VIEW_ASSETS,
                Permissions::VIEW_CATEGORIES,
                Permissions::VIEW_ASSET_TYPES,
                Permissions::VIEW_ASSET_HISTORY,
                Permissions::VIEW_REPORTS,
                Permissions::GENERATE_REPORTS,
                Permissions::VIEW_DASHBOARD,
                Permissions::AUDIT_LOGS,
            ],
            self::EMPLOYEE => [
                Permissions::VIEW_ASSETS,
                Permissions::VIEW_CATEGORIES,
                Permissions::VIEW_ASSET_TYPES,
                Permissions::VIEW_DASHBOARD,
            ],
        };
    }

    public function getPermissionNames(): array
    {
        return array_map(
            fn (Permissions $permission) => $permission->value,
            $this->getPermissions()
        );
    }
}