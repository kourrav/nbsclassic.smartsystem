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

class ManageAgentsController extends Controller
{
    public function allAgents()
    {
      $pageTitle = 'Manage Agents';
      $emptyMessage = 'No agents found';
      $agents = User::select('users.*','agents.commision')
      ->join('agents', 'agents.user_id', '=', 'users.id')
      ->where('users.category', 2)
      ->orderBy('id','desc')
      ->paginate(getPaginate());
      return view('admin.agents.list', compact('pageTitle', 'emptyMessage', 'agents'));
    }

    //Add Agent Functionality
    public function createAgent()
    {
        $pageTitle = 'Add Agent';
        $bloodgroup = bloodGroupList();
        $documenttype = documentType();
        $countries = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        return view('admin.agents.addagent', compact('pageTitle', 'countries','bloodgroup','documenttype'));
    }

    public function storeAgent(Request $request)
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

    public function activeUsers()
    {
        $pageTitle = 'Manage Active Users';
        $emptyMessage = 'No active user found';
        $users = User::active()->orderBy('id','desc')->paginate(getPaginate());
        return view('admin.users.list', compact('pageTitle', 'emptyMessage', 'users'));
    }

    public function bannedUsers()
    {
        $pageTitle = 'Banned Users';
        $emptyMessage = 'No banned user found';
        $users = User::banned()->orderBy('id','desc')->paginate(getPaginate());
        return view('admin.users.list', compact('pageTitle', 'emptyMessage', 'users'));
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

    public function showEmailSingleForm($id)
    {
        $user = User::findOrFail($id);
        $pageTitle = 'Send Email To: ' . $user->username;
        return view('admin.users.email_single', compact('pageTitle', 'user'));
    }

    public function sendEmailSingle(Request $request, $id)
    {
      $request->validate([
          'message' => 'required|string|max:65000',
          'subject' => 'required|string|max:190',
      ]);
      $user = User::findOrFail($id);
      sendGeneralEmail($user->email, $request->subject, $request->message, $user->username);
      $notify[] = ['success', $user->username . ' will receive an email shortly.'];
      return back()->withNotify($notify);
    }

    public function transactionDetails(Request $request, $id){
      //$email = EmailLog::findOrFail($id);
      $pageTitle = 'Transaction Details';
      echo "Under progress";
      die('');
      return view('admin.users.email_details', compact('pageTitle','email'));
    }

    public function userLoginHistory($id)
    {
        $user = User::findOrFail($id);
        $pageTitle = 'User Login History - ' . $user->username;
        $emptyMessage = 'No users login found.';
        $login_logs = $user->login_logs()->orderBy('id','desc')->with('user')->paginate(getPaginate());
        return view('admin.users.logins', compact('pageTitle', 'emptyMessage', 'login_logs'));
    }

    public function showEmailAllForm()
    {
        $pageTitle = 'Send Email To All Users';
        return view('admin.users.email_all', compact('pageTitle'));
    }

    public function sendEmailAll(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:65000',
            'subject' => 'required|string|max:190',
        ]);

        foreach (User::where('status', 1)->cursor() as $user) {
            sendGeneralEmail($user->email, $request->subject, $request->message, $user->username);
        }

        $notify[] = ['success', 'All users will receive an email shortly.'];
        return back()->withNotify($notify);
    }

    public function login($id){
        $user = User::findOrFail($id);
        Auth::login($user);
        return redirect()->route('user.home');
    }

    public function emailLog($id){
        $user = User::findOrFail($id);
        $pageTitle = 'Email log of '.$user->username;
        $logs = EmailLog::where('user_id',$id)->with('user')->orderBy('id','desc')->paginate(getPaginate());
        $emptyMessage = 'No data found';
        return view('admin.users.email_log', compact('pageTitle','logs','emptyMessage','user'));
    }







}
