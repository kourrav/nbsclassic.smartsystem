<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class ManageUsersController extends Controller
{
    public function allUsers()
    {
        $pageTitle = 'Manage Users';
        $emptyMessage = 'No user found';
        $users = User::orderBy('id','desc')->where('category','!=',2)->paginate(getPaginate());
        return view('admin.users.list', compact('pageTitle', 'emptyMessage', 'users'));
    }

    public function activeUsers()
    {
        $pageTitle = 'Manage Active Users';
        $emptyMessage = 'No active user found';
        $users = User::active()->orderBy('id','desc')->where('category','!=',2)->paginate(getPaginate());
        return view('admin.users.list', compact('pageTitle', 'emptyMessage', 'users'));
    }

    public function bannedUsers()
    {
        $pageTitle = 'Banned Users';
        $emptyMessage = 'No banned user found';
        $users = User::banned()->orderBy('id','desc')->where('category','!=',2)->paginate(getPaginate());
        return view('admin.users.list', compact('pageTitle', 'emptyMessage', 'users'));
    }

    public function emailUnverifiedUsers()
    {
        $pageTitle = 'Email Unverified Users';
        $emptyMessage = 'No email unverified user found';
        $users = User::emailUnverified()->where('category','!=',2)->orderBy('id','desc')->paginate(getPaginate());
        return view('admin.users.list', compact('pageTitle', 'emptyMessage', 'users'));
    }

    public function emailVerifiedUsers()
    {
        $pageTitle = 'Email Verified Users';
        $emptyMessage = 'No email verified user found';
        $users = User::emailVerified()->where('category','!=',2)->orderBy('id','desc')->paginate(getPaginate());
        return view('admin.users.list', compact('pageTitle', 'emptyMessage', 'users'));
    }

    public function smsUnverifiedUsers()
    {
        $pageTitle = 'SMS Unverified Users';
        $emptyMessage = 'No sms unverified user found';
        $users = User::smsUnverified()->where('category','!=',2)->orderBy('id','desc')->paginate(getPaginate());
        return view('admin.users.list', compact('pageTitle', 'emptyMessage', 'users'));
    }

    public function smsVerifiedUsers()
    {
        $pageTitle = 'SMS Verified Users';
        $emptyMessage = 'No sms verified user found';
        $users = User::smsVerified()->where('category','!=',2)->orderBy('id','desc')->paginate(getPaginate());
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
        }elseif($scope == 'emailUnverified'){
            $pageTitle = 'Email Unverified ';
            $users = $users->where('ev', 0);
        }elseif($scope == 'smsUnverified'){
            $pageTitle = 'SMS Unverified ';
            $users = $users->where('sv', 0);
        }elseif($scope == 'withBalance'){
            $pageTitle = 'With Balance ';
            $users = $users->where('balance','!=',0);
        }

        $users = $users->where('category','!=',2)->paginate(getPaginate());
        $pageTitle .= 'User Search - ' . $search;
        $emptyMessage = 'No search result found';
        return view('admin.users.list', compact('pageTitle', 'search', 'scope', 'emptyMessage', 'users'));
    }

    public function detail($id)
    {
        $pageTitle = 'User Details';
        $user = User::findOrFail($id);
        $categories = Category::all()->where('status',1)->where('id','!=',2);
        $countries = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        return view('admin.users.detail', compact('pageTitle', 'user','countries','categories'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        //echo "<pre>"; print_r($request->all()); die('test');
        $countryData = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $request->validate([
            'firstname' => 'required|max:50',
            'lastname' => 'required|max:50',
            //'email' => 'required|email|max:90|unique:users,email,' . $user->id,
            'mobile' => 'required|unique:users,mobile,' . $user->id,
            'country' => 'required',
            'category' => 'required',
        ]);

        $countryCode = $request->country;
        $user->mobile = $request->mobile;
        $user->country_code = $countryCode;
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        //$user->email = $request->email;
        $user->category = $request->category;
        $user->address = [
                        'address' => $request->address,
                        'city' => $request->city,
                        'state' => $request->state,
                        'zip' => $request->zip,
                        'country' => @$countryData->$countryCode->country,
                      ];
        $user->status = $request->status ? 1 : 0;
        $user->ev = $request->ev ? 1 : 0;
        $user->sv = $request->sv ? 1 : 0;
        $user->save();
        $notify[] = ['success', 'User details has been updated'];
        return redirect()->back()->withNotify($notify);
    }

    public function userLoginHistory($id)
    {
        $user = User::findOrFail($id);
        $pageTitle = 'User Login History - ' . $user->username;
        $emptyMessage = 'No users login found.';
        $login_logs = $user->login_logs()->orderBy('id','desc')->with('user')->paginate(getPaginate());
        return view('admin.users.logins', compact('pageTitle', 'emptyMessage', 'login_logs'));
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

    public function emailDetails($id){
        $email = EmailLog::findOrFail($id);
        $pageTitle = 'Email details';
        return view('admin.users.email_details', compact('pageTitle','email'));
    }

    //Add User Functionality

    public function createUser()
    {
      $pageTitle = 'Add User';
      $categories = Category::all()->where('status',1)->where('id','!=',2);
      $countries = json_decode(file_get_contents(resource_path('views/partials/country.json')));
      return view('admin.users.adduser', compact('pageTitle', 'countries','categories'));
    }


    public function storeUser(Request $request)
    {
        //echo "<pre>"; print_r($request->all()); die('test');
        $countryData = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $request->validate([
            'firstname' => 'required|max:50',
            'lastname' => 'required|max:50',
            'email' => 'required|email|max:90|unique:users,email',
            'mobile' => 'required|unique:users,mobile',
            'country' => 'required',
            'category' => 'required',
            'password' => 'required',
        ]);
        $user = new User();
        $countryCode = $request->country;
        $user->mobile = $request->mobile;
        $user->country_code = $countryCode;
        $user->username = strtolower($request->firstname);
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->email = $request->email;
        $user->category = $request->category;
        $user->password = Hash::make($request->password);
        $user->address = [
                        // 'address' => $request->address,
                        // 'city' => $request->city,
                        // 'state' => $request->state,
                        // 'zip' => $request->zip,
                        'country' => @$countryData->$countryCode->country,
                      ];
        $user->status = $request->status ? 1 : 0;
        $user->ev = $request->ev ? 1 : 0;
        $user->sv = $request->sv ? 1 : 0;
        $user->save();
        $notify[] = ['success', 'User has been created'];
        return redirect('admin/users')->withNotify($notify);
    }


    //Users Type New Section 10-03-2023 By Ravinder Kaur
    public function usersRole()
    {
        $pageTitle = 'Manage Roles';
        $emptyMessage = 'No roles found';
        $userstype = Category::orderBy('id','asc')->paginate(getPaginate(5));
        return view('admin.roles.role', compact('pageTitle', 'emptyMessage', 'userstype'));
    }

    public function usersRoleSearch(Request $request){
        $search = $request->search;
        //echo "<pre>"; print_r($search); die;
        $pageTitle = 'Role - '. $search;
        $emptyMessage = 'No roles found';
        $userstype = Category::where(function ($usertype) use ($search) {
            $usertype->where('name', 'like', "%$search%");
        });
        $userstype = $userstype->paginate(getPaginate(5));
        return view('admin.roles.role', compact('pageTitle', 'emptyMessage', 'userstype', 'search'));
    }

    public function usersRoleStore(Request $request){
        $this->validate($request,[
          'name'        => 'required|string|unique:user_categories',
          'description' => 'required|string',
          //'booking_permission' => 'booking_permission|string',
        ]);

        $category       = new Category();
        $category->name = $request->name;
        $category->description = $request->description;
        $booking_permission =  $request->booking_permission ? $request->booking_permission : array();
        $booking_string = "";
        if(!empty($booking_permission))
        {
          $booking_string = implode(',',$booking_permission);
        }
        $category->role_permission = $booking_string;
        $category->save();
        $notify[] = ['success', 'Role save successfully.'];
        return back()->withNotify($notify);
    }

    public function usersRoleUpdate(Request $request,$id){
        $this->validate($request,[
          'name'        => 'required|string',
          'description' => 'required|string',
          //'booking_permission' => 'booking_permission|string',
        ]);
        //echo "<pre>"; print_r($request->all()); die('test');
        $category = Category::find($id);
        $category->name = $request->name;
        $category->description = $request->description;
        $booking_permission =  $request->booking_permission_edit ? $request->booking_permission_edit : array();
        $booking_string = "";
        if(!empty($booking_permission))
        {
          $booking_string = implode(',',$booking_permission);
        }
        $category->role_permission = $booking_string;
        $category->save();

        $notify[] = ['success', 'Role update successfully.'];
        return back()->withNotify($notify);
    }

    public function usersRoleActiveDisabled(Request $request){
        $request->validate(['id' => 'required|integer']);
        $resultUsers = User::orderBy('id','desc')->where('category',$request->id)->first();
        if(!empty($resultUsers))
        {
          $notify[] = ['error', 'Sorry! This action can not perfomed, as user exists with this role.'];
        }
        else
        {
          $category = Category::find($request->id);
          $category->status = $category->status == 1 ? 0 : 1;
          $category->save();
          if($category->status == 1){
              $notify[] = ['success', 'Role active successfully.'];
          }else{
              $notify[] = ['success', 'Role disabled successfully.'];
          }
        }
        return back()->withNotify($notify);
    }

}
