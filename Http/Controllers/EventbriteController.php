<?php

namespace Modules\Eventbrite\Http\Controllers;

use App\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Modules\Eventbrite\Api\Eventbrite;
use Modules\Eventbrite\Entities\Mailbox;
use Modules\Eventbrite\Api\EventbriteClient;
use Modules\Eventbrite\Api\Exception\EventbriteException;
use Modules\Eventbrite\Entities\EventbriteSetting;

class EventbriteController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Summary of index
     * @param mixed $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index($id)
    {
        $mailbox = Mailbox::findOrFail($id);
        $mailbox = $mailbox->load('eventbriteSetting');

        $user = auth()->user();

        if (!$user->can('updateSettings', $mailbox)) {
          \Helper::denyAccess();
        }

        try {
            if (isset($mailbox->eventbriteSetting->eventbrite_secret_key)) {
                $mailbox->eventbriteSetting->eventbrite_secret_key = Crypt::decryptString($mailbox->eventbriteSetting->eventbrite_secret_key);
            }
        } catch (DecryptException $e) {
            $mailbox->eventbriteSetting->eventbrite_secret_key = '';
            \Session::flash('flash_error_floating', $e->getMessage());
        }

        return view('eventbrite::eventbrite_settings', ['mailbox' => $mailbox]);
    }

    /**
     * Update eventbrite key
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function update($id, Request $request)
    {

        $this->validate($request, [
            'eventbrite_secret_key' => 'required|max:250',
            'eventbrite_org_id' => 'required|max:250',
        ]);

        $mailbox = Mailbox::findOrFail($id);

        $mailbox = $mailbox->load('eventbriteSetting');

        /**
         * @var $user App\User
         */
        $user = auth()->user();

        if (!$user->can('updateSettings', $mailbox)) {
          \Helper::denyAccess();
        }
        else {
          try {
            if (!$this->checkCredential($request->eventbrite_secret_key, $request->eventbrite_org_id)) {
              \Session::flash('flash_error_floating', 'Unknown error.');
              return redirect()->back();
            }
          } catch ( \Exception | EventbriteException $e) {
            \Session::flash('flash_error_floating', method_exists($e, 'getErrorDescription') ? $e->getErrorDescription() : $e->getMessage());
            return redirect()->back();
          }
          $requestData = [
            'mailbox_id'   => $mailbox->id,
            'eventbrite_secret_key' =>  Crypt::encryptString($request->eventbrite_secret_key),
            'eventbrite_org_id' => $request->eventbrite_org_id,
          ];

          try {
            $mailbox->eventbriteSetting()->updateOrCreate(
              ['mailbox_id'   => $requestData['mailbox_id']],
              $requestData
            );
            \Session::flash('flash_success_floating', __('Secret Key Updated Successfully'));
          } catch (DecryptException $th) {
            \Session::flash('flash_error_floating', __($th->getMessage()));
          }

          return redirect()->route('eventbrite.settings', $mailbox->id);
        }
        }

    /**
    *  Delete eventbrite key
    * @param Request $request
    * @return void
    */
    public function destroy(Request $request, Mailbox $mailbox)
    {
        try {
            $mailbox->eventbriteSetting()->delete();
            \Session::flash('flash_success_floating', __('Secret Key delete Successfully'));
        } catch (DecryptException $th) {
            \Session::flash('flash_error_floating', __($th->getMessage()));
        }

        return redirect()->back();
    }

    public function refresh($mailboxId, $customerId) {

      $eventbriteSettings = EventbriteSetting::select('eventbrite_org_id')->where('mailbox_id', $mailboxId)->first();
      $customer = Customer::find($customerId);

      if (!$eventbriteSettings || !$customer) {
        return redirect()->back();
      }

      $eventbrite = new Eventbrite('', $eventbriteSettings->eventbrite_org_id);

      $eventbrite->clearCache($customer->getMainEmail());

      return redirect()->back();

    }

    private function checkCredential($credential, $orgId)
    {
            $response = EventbriteClient::init($credential, $orgId)->checkConnection();

            if($response) {
                return true;
            }
            return false;
    }
}
