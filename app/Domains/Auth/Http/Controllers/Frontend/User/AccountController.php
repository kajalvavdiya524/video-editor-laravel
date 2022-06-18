<?php

namespace App\Domains\Auth\Http\Controllers\Frontend\User;

use App\Http\Controllers\Controller;
use App\Domains\Auth\Models\Customer;

/**
 * Class AccountController.
 */
class AccountController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $customers = $this->get_customers();
        return view('frontend.user.account', ['customers' => $customers]);
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
