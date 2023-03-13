<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\User;
use App\Models\Agent;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ManagePermissionsController extends Controller
{
    public function allPermissions()
    {
      $pageTitle = 'Manage Permissions';
      $emptyMessage = 'No Permissions found';
      $agents = User::select('users.*','agents.commision')
      ->join('agents', 'agents.user_id', '=', 'users.id')
      ->where('users.category', 2)
      ->orderBy('id','desc')
      ->paginate(getPaginate());
      return view('admin.permissions.list', compact('pageTitle', 'emptyMessage', 'agents'));
    }

    //Add Agent Functionality
    public function createPermission()
    {
        $pageTitle = 'Add Permission';
        $bloodgroup = bloodGroupList();
        $documenttype = documentType();
        $countries = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        return view('admin.permissions.addpermission', compact('pageTitle', 'countries','bloodgroup','documenttype'));
    }

    public function storePermission(Request $request)
    {
        //echo "<pre>"; print_r($request->all()); die('test');
        $countryData = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $request->validate([
            'firstname' => 'required|max:50',
            'lastname' => 'required|max:50',
            'email' => 'required|email|max:90|unique:users,email',
            'mobile' => 'required|unique:users,mobile',
            'country' => 'required',
            'id_type' => 'required',
            'id_number' => 'required',
            'blood' => 'required',
            'commission' => 'required',
        ]);

        $user = new User();
        $countryCode = $request->country;
        $user->mobile = $request->mobile;
        $user->country_code = $countryCode;
        $user->username = strtolower($request->firstname);
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->email = $request->email;
        $user->category = 2;
        $user->password = Hash::make($request->password);
        $user->address = [
                        'address' => $request->address,
                        'city' => $request->city,
                        'state' => $request->state,
                        'zip' => $request->zip,
                        'country' => @$countryData->$countryCode->country,
                      ];
        $user->status = $request->status ? 1 : 0;
        $user->ev = 1;
        $user->sv = 1;
        $user->save();
        $user_ID = $user->id;
        $agent = new Agent();
        $agent->user_id = $user_ID;
        $agent->id_type = $request->id_type;
        $agent->id_number = $request->id_number;
        $agent->blood = $request->blood;
        $agent->commision = $request->commission;
        $agent->save();
        $notify[] = ['success', 'Agent has been created'];
        return redirect('admin/agents')->withNotify($notify);
    }

    public function search(Request $request, $scope)
    {
        $search = $request->search;
        $users = User::where(function ($user) use ($search) {
            $user->where('username', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%");
        });
        $pageTitle = '';
        if ($scope == 'active') {
            $pageTitle = 'Active ';
            $users = $users->where('status', 1);
        }elseif($scope == 'banned'){
            $pageTitle = 'Banned';
            $users = $users->where('status', 0);
        }
        $users = $users->where('category',2)->paginate(getPaginate());
        $pageTitle .= 'Agent Search - ' . $search;
        $emptyMessage = 'No search result found';
        return view('admin.users.list', compact('pageTitle', 'search', 'scope', 'emptyMessage', 'users'));
    }

    public function detail($id)
    {
      $pageTitle = 'Agent Details';
      $user      = User::select('users.*','agents.id_type','agents.id_number','agents.blood','agents.commision')
      ->join('agents', 'agents.user_id', '=', 'users.id')
      ->where('users.category', 2)
      ->findOrFail($id);
      $bloodgroup = bloodGroupList();
      $documenttype = documentType();
      $countries = json_decode(file_get_contents(resource_path('views/partials/country.json')));
      return view('admin.agents.detail', compact('pageTitle', 'user','countries','bloodgroup','documenttype'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        //echo "<pre>"; print_r($request->all()); die('test');
        $countryData = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $request->validate([
          'firstname' => 'required|max:50',
          'lastname' => 'required|max:50',
          'mobile' => 'required|unique:users,mobile,'.$id,
          'country' => 'required',
          'id_type' => 'required',
          'id_number' => 'required',
          'blood' => 'required'
        ]);
        $user_ID = $user->id;
        $countryCode = $request->country;
        $user->mobile = $request->mobile;
        $user->country_code = $countryCode;
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->address = [
                        'address' => $request->address,
                        'city' => $request->city,
                        'state' => $request->state,
                        'zip' => $request->zip,
                        'country' => @$countryData->$countryCode->country,
                      ];
        $user->status = $request->status ? 1 : 0;
        $user->save();
        $agent = Agent::where('user_id',$user_ID)->first();
        $agent->id_type = $request->id_type;
        $agent->id_number = $request->id_number;
        $agent->blood = $request->blood;
        $agent->commision = $request->commission;
        $agent->save();
        $notify[] = ['success', 'Agent details has been updated'];
        return redirect()->back()->withNotify($notify);
    }

    







}
