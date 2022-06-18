<?php

namespace App\Domains\Auth\Http\Controllers\Backend\User;

use App\Domains\Auth\Http\Requests\Backend\User\DeleteUserRequest;
use App\Domains\Auth\Http\Requests\Backend\User\EditUserRequest;
use App\Domains\Auth\Http\Requests\Backend\User\StoreUserRequest;
use App\Domains\Auth\Http\Requests\Backend\User\UpdateUserRequest;
use App\Domains\Auth\Models\User;
use App\Domains\Auth\Models\Customer;
use App\Domains\Auth\Services\PermissionService;
use App\Domains\Auth\Services\RoleService;
use App\Domains\Auth\Services\UserService;
use App\Domains\Auth\Services\CompanyService;
use App\Http\Controllers\Controller;

/**
 * Class UserController.
 */
class UserController extends Controller
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var RoleService
     */
    protected $roleService;

    /**
     * @var PermissionService
     */
    protected $permissionService;

    /**
     * @var CompanyService
     */
    protected $companyService;

    /**
     * UserController constructor.
     *
     * @param  UserService  $userService
     * @param  RoleService  $roleService
     * @param  PermissionService  $permissionService
     */
    public function __construct(UserService $userService, RoleService $roleService, PermissionService $permissionService, CompanyService $companyService)
    {
        $this->userService = $userService;
        $this->roleService = $roleService;
        $this->permissionService = $permissionService;
        $this->companyService = $companyService;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('backend.auth.user.index');
    }

    /**
     * @return mixed
     */
    public function create()
    {
        $customers = $this->get_customers();
        return view('backend.auth.user.create')
            ->withCompanies($this->companyService->all())
            ->withRoles($this->roleService->with('permissions')->get())
            ->withCategories($this->permissionService->getCategorizedPermissions())
            ->withGeneral($this->permissionService->getUncategorizedPermissions())
            ->withCustomers($customers);
    }

    /**
     * @param  StoreUserRequest  $request
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     * @throws \Throwable
     */
    public function store(StoreUserRequest $request)
    {
        $user = $this->userService->store($request->validated());

        return redirect()->route('admin.auth.user.show', $user)->withFlashSuccess(__('The user was successfully created.'));
    }

    /**
     * @param  User  $user
     *
     * @return mixed
     */
    public function show(User $user)
    {
        return view('backend.auth.user.show')
            ->withUser($user);
    }

    /**
     * @param  EditUserRequest  $request
     * @param  User  $user
     *
     * @return mixed
     */
    public function edit(EditUserRequest $request, User $user)
    {
        $customers = $this->get_customers();
        return view('backend.auth.user.edit')
            ->withUser($user)
            ->withCompanies($this->companyService->all())
            ->withRoles($this->roleService->with('permissions')->get())
            ->withCategories($this->permissionService->getCategorizedPermissions())
            ->withGeneral($this->permissionService->getUncategorizedPermissions())
            ->withUsedPermissions($user->permissions->modelKeys())
            ->withCustomers($customers);
    }

    /**
     * @param  UpdateUserRequest  $request
     * @param  User  $user
     *
     * @return mixed
     * @throws \Throwable
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $this->userService->update($user, $request->validated());

        return redirect()->route('admin.auth.user.show', $user)->withFlashSuccess(__('The user was successfully updated.'));
    }

    /**
     * @param  DeleteUserRequest  $request
     * @param  User  $user
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     */
    public function destroy(DeleteUserRequest $request, User $user)
    {
        $user->teams()->sync(array());
        $this->userService->delete($user);

        return redirect()->route('admin.auth.user.deleted')->withFlashSuccess(__('The user was successfully deleted.'));
    }

    private function get_customers()
    {
        $customers = [];
        $user = auth()->user();
        if ((! $user->isMember()) || (! $user->isTeamMember())) {
            if ($user->isMasterAdmin()) {
                $customers = Customer::all();
            } else {
                $customers = Customer::where('system', 1)->get();
                $customers = $customers->merge($user->company->customers);
            }
        } else {
            foreach ($user->teams as $team) {
                foreach ($team->customers as $customer) {
                    $customers[] = $customer;
                }
            }
            
            $models = array_map( function( $customer ) {
                return $customer->id;
                }, $customers );
            $unique_models = array_unique( $models );
            $customers = array_values( array_intersect_key( $customers, $unique_models ) );
        }
        if (!$user->isMasterAdmin() && !$user->company->has_mrhi) {
            $cus = array();
            foreach($customers as $customer) {
                if ($customer->value != 'mrhi') {
                    $cus[] = $customer;
                }
            }
            return $cus;
        }
        return $customers;
    }
}
