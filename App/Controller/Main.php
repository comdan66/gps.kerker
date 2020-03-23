<?php

class Main extends Controller {

  public function index() {
    // if (!$data = $this->cover(Input::get('v')))
    //   return;

    // \M\Signal::create([
    //   'lat' => $data['lat'],
    //   'lng' => $data['lng'],
    //   'memo' => json_encode($data),
    // ]);

    \M\Signal::createByGet();
    return 'ok';
  }

  public function map() {
    // $signals = \M\Signala::all(['select' => 'lat,lng,memo', 'order' => 'id DESC']);
    
    // foreach ($signals as $signal) {
      // $memo = json_decode($signal->memo, true);
      // \M\Signal::
      // echo $memo['course'] . " - " . $memo['speed'] . " - " . ($memo['speed'] * 1.852) . " - " . ($memo['speed'] * 3.6) . '<br>';
    // }

    // $str = '$GPRMC,,V,,,,,,,,,,N*53';
    // $str = '$GPRMC,061009.000,A,2459.8682,N,12131.2094,E,0.0,0.0,220320,3.1,W,A*1B';
    // $str = '$GPRMC,071513.000,A,2459.8716,N,12131.2233,E,0.0,0.0,220320,3.1,W,A*17';
    // $signals = array_map('\M\toArray', \M\Signal::all(['select' => 'lat,lng', 'order' => 'id DESC']));
    // return View::create('map.php')->with('signals', $signals);
  }
}