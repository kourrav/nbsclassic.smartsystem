<?php
namespace App\Http\Controllers\Admin;

use App\Lib\BusLayout;
use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\User;
use App\Models\Agent;
use App\Models\Category;
use App\Models\Trip;
use App\Models\TicketPrice;
use App\Models\BookedTicket;
use App\Models\VehicleRoute;
use App\Models\Counter;
use App\Models\FleetType;
use App\Models\Frontend;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ManageTicketBookingController extends Controller
{
  public function __construct(){
      $this->activeTemplate = activeTemplate();
  }
  public function searchTicket(){
    //echo"<pre>"; print_r($trips); die(); echo"<pre>";
      $pageTitle = 'Search Tickets';
      $emptyMessage = 'There is no trip available';
      $fleetType = FleetType::active()->get();
      $trips = Trip::with(['fleetType' ,'route', 'schedule', 'startFrom' , 'endTo'])->where('status', 1)->paginate(getPaginate(10));
      $trips = array();
      if(auth()->user()){
          $layout = 'layouts.master';
      }else{
          $layout = 'layouts.frontend';
      }
      $schedules = Schedule::all();
      $routes = VehicleRoute::active()->get();
      return view('admin.ticket_booking.ticket', compact('pageTitle' ,'fleetType', 'trips','routes' ,'schedules', 'emptyMessage', 'layout'));
  }

  public function ticketSearch(Request $request)
  {
    //echo "<pre>"; print_r($request->all()); echo "</pre>"; die('tester');
      if($request->pickup == "" && $request->destination == ""){
          $notify[] = ['error', 'Please select pickup point and destination point'];
          return redirect()->back()->withNotify($notify);
      }
      if($request->pickup && $request->destination && $request->pickup == $request->destination){
          $notify[] = ['error', 'Please select pickup point and destination point properly'];
          return redirect()->back()->withNotify($notify);
      }
      if($request->date_of_journey && Carbon::parse($request->date_of_journey)->format('Y-m-d') < Carbon::now()->format('Y-m-d')){
          $notify[] = ['error', 'Date of journey can\'t be less than today.'];
          return redirect()->back()->withNotify($notify);
      }

      $trips = Trip::active();

      if($request->pickup && $request->destination){
          Session::put('pickup', $request->pickup);
          Session::put('destination', $request->destination);
          //echo"<pre>";print_r();die();echo"<pre>";
          $pickup = $request->pickup;
          $destination = $request->destination;
          $trips = $trips->with('route')->get();
          $tripArray = array();

          foreach ($trips as $trip) {
              $startPoint = array_search($trip->start_from , array_values($trip->route->stoppages));
              $endPoint = array_search($trip->end_to , array_values($trip->route->stoppages));
              $pickup_point = array_search($pickup , array_values($trip->route->stoppages));
              $destination_point = array_search($destination , array_values($trip->route->stoppages));
              if($startPoint < $endPoint){
                  if($pickup_point >= $startPoint && $pickup_point < $endPoint && $destination_point > $startPoint && $destination_point <= $endPoint){
                      array_push($tripArray, $trip->id);
                  }
              }else{
                  $revArray = array_reverse($trip->route->stoppages);
                  $startPoint = array_search($trip->start_from ,array_values($revArray));
                  $endPoint = array_search($trip->end_to ,array_values($revArray));
                  $pickup_point = array_search($pickup ,array_values($revArray));
                  $destination_point = array_search($destination ,array_values($revArray));
                  if($pickup_point >= $startPoint && $pickup_point < $endPoint && $destination_point > $startPoint && $destination_point <= $endPoint){
                      array_push($tripArray, $trip->id);
                  }
              }
          }

          $trips = Trip::active()->whereIn('id',$tripArray);
      }else{
          if($request->pickup){
              Session::put('pickup', $request->pickup);
              $pickup = $request->pickup;
              $trips = $trips->whereHas('route' , function($route) use ($pickup){
                  $route->whereJsonContains('stoppages' , $pickup);
              });
          }

          if($request->destination){
              Session::put('destination', $request->destination);
              $destination = $request->destination;
              $trips = $trips->whereHas('route' , function($route) use ($destination){
                  $route->whereJsonContains('stoppages' , $destination);
              });
          }
      }

      if($request->fleetType){
          $trips = $trips->whereIn('fleet_type_id',$request->fleetType);
      }

      if($request->routes){
          $trips = $trips->whereIn('vehicle_route_id',$request->routes);
      }

      if($request->schedules){
          $trips = $trips->whereIn('schedule_id',$request->schedules);
      }

      if($request->date_of_journey){
          Session::put('date_of_journey', $request->date_of_journey);
          $dayOff = Carbon::parse($request->date_of_journey)->format('w');
          $trips = $trips->whereJsonDoesntContain('day_off', $dayOff);
      }

      $trips = $trips->with( ['fleetType' ,'route', 'schedule', 'startFrom' , 'endTo'] )->where('status', 1)->paginate(getPaginate());

      $pageTitle = 'Search Tickets';
      $emptyMessage = 'There is no trip available';
      $fleetType = FleetType::active()->get();
      $schedules = Schedule::all();
      $routes = VehicleRoute::active()->get();

      if(auth()->user()){
          $layout = 'layouts.master';
      }else{
          $layout = 'layouts.frontend';
      }
      return view('admin.ticket_booking.ticket', compact('pageTitle' ,'fleetType', 'trips','routes', 'schedules', 'emptyMessage', 'layout'));
  }

  public function showSeat($id){
    // echo "<pre>"; print_r(Session::all()); echo "</pre>"; die('test');
      $trip = Trip::with( ['fleetType' ,'route', 'schedule', 'startFrom' , 'endTo', 'assignedVehicle.vehicle', 'bookedTickets'])->where('status', 1)->where('id', $id)->firstOrFail();
      $pageTitle = $trip->title;
      $route     = $trip->route;
      $stoppageArr = $trip->route->stoppages;
      $stoppages = Counter::routeStoppages($stoppageArr);
      $busLayout = new BusLayout($trip);
      if(auth()->user()){
          $layout = 'layouts.master';
      }else{
          $layout = 'layouts.frontend';
      }
      $info = json_decode(json_encode(getIpInfo()), true);
      $mobile_code = @implode(',', $info['code']);
      $countries = json_decode(file_get_contents(resource_path('views/partials/country.json')));

      return view('admin.ticket_booking.book_ticket', compact('pageTitle','trip' , 'stoppages','busLayout', 'layout','mobile_code','countries'));
  }

  public function getTicketPrice(Request $request){
      $ticketPrice       = TicketPrice::where('vehicle_route_id', $request->vehicle_route_id)->where('fleet_type_id', $request->fleet_type_id)
      ->where('travel_class', $request->travel_class)->with('route')->first();
      //echo "<pre>"; print_r($ ); die();
      $route              = $ticketPrice->route;
      $stoppages          = $ticketPrice->route->stoppages;
      $trip               = Trip::find($request->trip_id);
      $sourcePos         = array_search($request->source_id, $stoppages);
      $destinationPos    = array_search($request->destination_id, $stoppages);

      $bookedTicket  = BookedTicket::where('trip_id', $request->trip_id)->where('date_of_journey', Carbon::parse($request->date)->format('Y-m-d'))->whereIn('status', [1,2])->get()->toArray();

      $startPoint = array_search($trip->start_from , array_values($trip->route->stoppages));
      $endPoint = array_search($trip->end_to , array_values($trip->route->stoppages));
      if($startPoint < $endPoint){
          $reverse = false;
      }else{
          $reverse = true;
      }

      if(!$reverse){
          $can_go = ($sourcePos < $destinationPos)?true:false;
      }else{
          $can_go = ($sourcePos > $destinationPos)?true:false;
      }

      if(!$can_go){
          $data = [
              'error' => 'Select Pickup Point & Dropping Point Properly'
          ];
          return response()->json($data);
      }
      $sdArray  = [$request->source_id, $request->destination_id];
      $getPrice = $ticketPrice->prices()->where('source_destination', json_encode($sdArray))->orWhere('source_destination', json_encode(array_reverse($sdArray)))->first();

      if($getPrice){
          $price = $getPrice->price;
      }else{
          $price = [
              'error' => 'Admin may not set prices for this route. So, you can\'t buy ticket for this trip.'
          ];
      }
      $data['bookedSeats']        = $bookedTicket;
      $data['reqSource']         = $request->source_id;
      $data['reqDestination']    = $request->destination_id;
      $data['reverse']            = $reverse;
      $data['stoppages']          = $stoppages;
      $data['price']              = $price;
      return response()->json($data);
  }

  public function bookTicket(Request $request,$id){
      $request->validate([
          "pickup_point"   => "required|integer|gt:0",
          "dropping_point"  => "required|integer|gt:0",
          "date_of_journey" => "required|date",
          "seats"           => "required|string",
          "gender"          => "required|integer",
          "price"           => "required"
      ],[
          "seats.required"  => "Please Select at Least One Seat"
      ]);

      if(!auth()->user()){
          $notify[] = ['error', 'Without login you can\'t book any tickets'];
          return redirect()->route('user.login')->withNotify($notify);
      }

      $date_of_journey  = Carbon::parse($request->date_of_journey);
      $today            = Carbon::today()->format('Y-m-d');
      if($date_of_journey->format('Y-m-d') < $today ){
          $notify[] = ['error', 'Date of journey cant\'t be less than today.'];
          return redirect()->back()->withNotify($notify);
      }

      $dayOff =  $date_of_journey->format('w');
      $trip   = Trip::findOrFail($id);
      $route              = $trip->route;
      $stoppages          = $trip->route->stoppages;
      $source_pos         = array_search($request->pickup_point, $stoppages);
      $destination_pos    = array_search($request->dropping_point, $stoppages);

      if(!empty($trip->day_off)) {
          if(in_array($dayOff, $trip->day_off)) {
              $notify[] = ['error', 'The trip is not available for '.$date_of_journey->format('l')];
              return redirect()->back()->withNotify($notify);
          }
      }

      $booked_ticket  = BookedTicket::where('trip_id', $id)->where('date_of_journey', Carbon::parse($request->date)->format('Y-m-d'))->whereIn('status',[1,2])->where('pickup_point', $request->pickup_point)->where('dropping_point', $request->dropping_point)->whereJsonContains('seats', rtrim($request->seats, ","))->get();
      if($booked_ticket->count() > 0){
          $notify[] = ['error', 'Why you are choosing those seats which are already booked?'];
          return redirect()->back()->withNotify($notify);
      }

      $startPoint = array_search($trip->start_from , array_values($trip->route->stoppages));
      $endPoint = array_search($trip->end_to , array_values($trip->route->stoppages));
      if($startPoint < $endPoint){
          $reverse = false;
      }else{
          $reverse = true;
      }

      if(!$reverse){
          $can_go = ($source_pos < $destination_pos)?true:false;
      }else{
          $can_go = ($source_pos > $destination_pos)?true:false;
      }

      if(!$can_go){
          $notify[] = ['error', 'Select Pickup Point & Dropping Point Properly'];
          return redirect()->back()->withNotify($notify);
      }

      $route = $trip->route;
      $ticketPrice = TicketPrice::where('fleet_type_id', $trip->fleetType->id)->where('vehicle_route_id', $route->id)->first();
      $sdArray     = [$request->pickup_point, $request->dropping_point];

      $getPrice    = $ticketPrice->prices()
                  ->where('source_destination', json_encode($sdArray))
                  ->orWhere('source_destination', json_encode(array_reverse($sdArray)))
                  ->first();
      if (!$getPrice) {
          $notify[] = ['error','Invalid selection'];
          return back()->withNotify($notify);
      }
      $seats = array_filter((explode(',', $request->seats)));
      $unitPrice = getAmount($getPrice->price);
      $pnr_number = getTrx(10);
      $bookedTicket = new BookedTicket();
      $bookedTicket->user_id = auth()->user()->id;
      $bookedTicket->gender = $request->gender;
      $bookedTicket->trip_id = $trip->id;
      $bookedTicket->source_destination = [$request->pickup_point, $request->dropping_point];
      $bookedTicket->pickup_point = $request->pickup_point;
      $bookedTicket->dropping_point = $request->dropping_point;
      $bookedTicket->seats = $seats;
      $bookedTicket->ticket_count = sizeof($seats);
      $bookedTicket->unit_price = $unitPrice;
      $bookedTicket->sub_total = sizeof($seats) * $unitPrice;
      $bookedTicket->date_of_journey = Carbon::parse($request->date_of_journey)->format('Y-m-d');
      $bookedTicket->pnr_number = $pnr_number;
      $bookedTicket->status = 0;
      $bookedTicket->save();
      session()->put('pnr_number',$pnr_number);
      return redirect()->route('user.deposit');
  }





}
