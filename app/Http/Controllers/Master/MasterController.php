<?php

namespace App\Http\Controllers\Master;

use App\Enums\AccountCategoryEnum;
use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Services\Master\AccountService;
use App\Services\Master\AccountSettingService;
use App\Services\Master\ContactService;
use App\Services\ErpDataService;
use App\Services\Master\ProductService;
use App\Services\Master\RoleService;
use Spatie\Permission\Models\Permission;

class MasterController extends Controller
{
    public function index(ProductService $productService, ContactService $contactService, AccountService $accountService, RoleService $roleService, AccountSettingService $accountSettingService)
    {
        $roleList = $roleService->fetchRoleData()->map(fn($r) => [
            'id'          => $r->id,
            'name'        => $r->name,
            'permissions' => $r->permissions->pluck('name')->values(),
        ]);

        // Build permission tree: module -> resource -> [actions]
        $permissionTree = [];
        foreach (Permission::orderBy('name')->get(['name']) as $p) {
            [$module, $resource, $action] = array_pad(explode('.', $p->name, 3), 3, '');
            $permissionTree[$module][$resource][] = $action;
        }

        $companyID = config('context.selected_company_id');

        return view('master.index', [
            'currentPage'          => 'master',
            'breadcrumb'           => [['label' => 'Master Data']],
            'accountCategories'    => $accountService->fetchAccountCategoryData(),
            'userRoles'            => $roleService->fetchRoleData(),
            'roles'                => ErpDataService::roles(),
            'roleList'             => $roleList,
            'permissionTree'       => $permissionTree,
            'units'                => Unit::whereNull('deleted_at')->select('id', 'name', 'symbol')->get(),
            'productCategories'    => $productService->fetchProductCategoryData(),
            'contactOptions'       => $contactService->fetchContactData('employee'),
            'inventoryAccounts'    => $accountService->fetchAccountData(companyID: $companyID, categoryID: AccountCategoryEnum::INVENTORY->value),
            'salesAccounts'        => $accountService->fetchAccountData(companyID: $companyID, categoryID: AccountCategoryEnum::REVENUE->value),
            'cogsAccounts'         => $accountService->fetchAccountData(companyID: $companyID, categoryID: AccountCategoryEnum::COST_OF_GOODS_SOLD->value),
            'receivableAccounts'   => $accountService->fetchAccountData(companyID: $companyID, categoryID: AccountCategoryEnum::ACCOUNT_RECEIVABLE->value),
            'payableAccounts'      => $accountService->fetchAccountData(companyID: $companyID, categoryID: AccountCategoryEnum::ACCOUNT_PAYABLE->value),
            'allAccounts'          => $accountService->fetchAccountData(companyID: $companyID),
            'accountSettingGroups' => $accountSettingService->fetchAccountSettingGroups(),
            'accountSettingValues' => $accountSettingService->fetchAccountSettingValues(),
        ]);
    }

    
}
