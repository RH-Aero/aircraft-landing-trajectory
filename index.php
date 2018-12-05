<?php
include 'html/main_page.html';
ini_set('error_reporting', E_ALL);

  // airplane characteristics
  $S = 201.45; // m^2 - square
  $l = 37.55; // m - wingspan
  $b_a = 5.285; // m - average aerodynamic wing chord
  $al = 0.24; // pers AEwC - alignment
  $G0 = 73000; // kg - weight with fuel
  $G_f0 = 20000; // kg - fuel weight
  $q_eng = 0.585; // kg per s - fuel consumption for one engine
  $I_x = 250000; // kg * m * s^2 - cross moment of inertia
  $I_y = 900000; // kg * m * s^2 - roadway moment of inertia
  $I_z = 660000; // kg * m * s^2 - lengthwise moment of inertia
  $Dz = 17; // deg - angle of flaps
  $Fi_st = -2.5; // deg - angle of fin
  $pDz = 2; // deg per s

  // hs flight mode
  $V0 = 78; // m per s - speed // Vhf - speed of  horizontal flight
  $H0 = 500; // m - height
  $pr = 0.119; // (kg * s^2) per m^2 - pressure
  $An = 338.36; // m per s - sound velocity
  $Alpha_bal = 7.1; // deg
  $Tetta0 = 0; // deg
  $g = 9.81; // m per s^2 - gravitational acceleration
  $m = $G0 / $g; // N - Weight

  $P_1_Dg = 7003;
  $P_1_V = -13.8;

  $C_x = 0.13;
  $C_x_Alpha = 0.286;
  $C_x_M = 0;
  $C_xhf = 0.043;

  $C_y0 = -0.255;
  $C_y_Alpha = 5.78;
  $C_y_Dv = 0.2865;
  $C_y_Dz = 1.2222;
  $C_y_Fi = 0.715;
  $C_y_M = 0;
  $C_yhf = 0.6446;

  $C_z_B = -1.0715;
  $C_z_Dn = -0.183;

  $m_x_Dn = -0.0206;
  $m_x_vWy = -0.31;
  $m_x_vWx = -0.583;
  $m_x_B = -0.186;
  $m_x_De = -0.0688;

  $m_y_vWy = -0.21;
  $m_y_B = -0.2;
  $m_y_Dn = -0.0716;
  $m_y_De = 0;
  $m_y_vWx = -0.006;

  $m_z0 = 0.2;
  $m_z_vWz = -13;
  $m_z_vDAlpha = -3.8;
  $m_z_vAlpha = -1.38;
  $m_z_Alpha = -1.51;
  $m_z_Dv = -0.96;
  $m_z_M = 0;
  $m_z_Dz = -0.458;
  $m_z_Fi = 0.715;

  // automatic landing approach
  $k = array();
  $T = array();
  $Ipsilon_op = array();
  $T_op = array();
  $H_set = $H0;
  $k_Wz = 1.0;
  $k_Ipsilon = 1.0;
  $k_Ipsilon_set = 4.0;
  $k_integral = 0.0002;
  $k_H = 0.2;
  $k_DH = 0.4;
  $T_op[1] = 2.0;
  $T_DH = 1.0;
  $Ipsilon_op[1] = 5;
  $Ipsilon_op[2] = 2.5;
  ## F[1] = +- 10 dergees
  ## F[2] = +- 10 dergees
  ## F[3] = +- 8 dergees
  ## F[4] = +- 10 dergees
  $L_RWY = 3000; // m
  $S_LOCn = 167; // µA per dergee
  $DI_LOC = 0;
  $T_LOC = 0.2;
  $Psi_g0 = 90;
  $Tetta_GSn = 2.67;
  $S_GSn = 560; // µA per dergee
  $T_GS = 0.2;
  $DistanceZ = 3500;
  $k[2] = 210.0; // H > 250 m
  // $k[2] = 90.0; // 100 <= H <= 250 m
  // $k[2] = 90.0; // H < 100 m
  $k[4] = 1.0;
  $k[7] = 15.0; // H > 250 m
  // $k[7] = 6.5; // 100 <= H <= 250 m
  // $k[7] = 6.5; // H < 100 m
  $k[8] = 13.0;
  $k[11] = 6.0;
  $T[4] = 2.3; // H > 250 m
  // $T[4] = 2.3; // 100 <= H <= 250 m
  // $T[4] = 1.0; // H < 100 m
  ### $F[6] = +- 7.0; // H > 250 m
  ### $F[6] = +- 3.5; // 100 <= H <= 250 m
  ### $F[6] = +- 3.5; // H < 100 m

  // for calculations
  $DGp = (rad2deg($H0) / 2.67) - $DistanceZ - 300; // Gp - glide path
  $Ga_B = $m_y_B - (($C_z_B * $pr * $S * $l) / (4 * $m)) * $m_y_vWy;
  $W_x_De = -0.73;
  $Xx = (($m_x_B * $I_y) / ($m_y_B * $I_x)) * (1 / sqrt(1 - pow(($m_x_vWx / $I_x), 2) * $I_y * $S * pow($l, 2) * ($pr / (4 * $m_y_B))));
  $C_ybal = (2 * $G0) / ($S * $pr * pow($V0, 2));
  $A_bal = rad2deg(1) * (($C_ybal - $C_y0) / $C_y_Alpha);

  // coefficients for linear math. model of side plane profile
  $c[1] = -($m_z_vWz / $I_z) * $S * pow($b_a, 2) * (($pr * pow($V0, 1) / 2));
  $c[2] = -($m_z_Alpha / $I_z) * $S * pow($b_a, 1) * (($pr * pow($V0, 2) / 2));
  $c[3] = -($m_z_Dv / $I_z) * $S * pow($b_a, 1) * (($pr * pow($V0, 2) / 2));
  $c[4] = (($C_y_Alpha + $C_x) / $m) * $S * (($pr * pow($V0, 1) / 2));
  $c[5] = -($m_z_vDAlpha / $I_z) * $S * pow($b_a, 2) * (($pr * pow($V0, 1) / 2));
  $c[6] = $V0 / rad2deg(1);
  $c[9] = ($C_y_Dv / $m) * $S * (($pr * pow($V0, 1) / 2));
  $c[12] = -($m_z_Dz / $I_z) * $S * pow($b_a, 1) * (($pr * pow($V0, 2) / 2));
  $c[13] = ($C_y_Dz / $m) * $S * (($pr * pow($V0, 1) / 2));
  $c[16] = $V0 / (rad2deg(1) * $g);
  // $c[20] = rad2deg(1) * $C_y0 * $S * $b_a * (($pr * pow($V0, 2)) / (2 * $I_z));
  $c[21] = -($m_z_Fi / $I_z) * $S * pow($b_a, 1) * (($pr * pow($V0, 2) / 2));
  $c[22] = ($C_y_Fi / $m) * $S * (($pr * pow($V0, 1) / 2));

  //////////////////////////////////////
  ////////////Control Panel/////////////
  //////////////////////////////////////
  // $mode = "free flight kappa";     // 
  // $mode = "free flight De";        // 
  $mode = "regulation";            //
  //                                  //
  // $positioning_method = "course";  //
  // $positioning_method = "path";    //
  // $positioning_method = "way";     //
  //                                  //
  $integration_method = "eiler";   //
  //                                  //
  // $signal = "zero";                // 
  $signal = "normal";              //
  //////////////////////////////////////

  $graph_data = array_fill(1,6,array());

  for($flight_case = 1; $flight_case <= 6; $flight_case++) {

    $t = 0; // s - flight time
    $td = 0; // s - output time
    $tg = 0; // s - graphics output time
    $tf = 300.1; // s - flight ending time
    $dt = 0.01; // 1 per s - integration step
    $dd = 25; // s - output step
    $gd = 5; // s - graphics output step

    $X = array_fill(1, 17, 0);
    $Y = array_fill(1, 17, 0);
    $F = array_fill(1, 2, 0);
    $Y[5] = $H0; // m
    $Y[7] = 0; // m 18000

    switch($flight_case) {
      case 1 : {
        $Tetta_GS = $Tetta_GSn;
        $S_GS = 200;
      break;
      }
      case 2 : {
        $Tetta_GS = $Tetta_GSn;
        $S_GS = 560;
      break;
      }
      case 3 : {
        $Tetta_GS = $Tetta_GSn;
        $S_GS = 925;
      break;
      }
      case 4 : {
        $Tetta_GS = 2;
        $S_GS = $S_GSn;
      break;
      }
      case 5 : {
        $Tetta_GS = 2.67;
        $S_GS = $S_GSn;
      break;
      }
      case 6 : {
        $Tetta_GS = 4;
        $S_GS = $S_GSn;
      break;
      }
    }

    $W = 0;
    $NV = 0;
    $Dn = 0;
    $De = 0;
    $Dv = 0;
    $Epsilon_gs = 0;
    $I_gs = 0;

    echo "<div class=\"container no-pad-bot scrollspy\" id=\"flightcase-" . $flight_case ."\">
      <div class=\"section\">
        <h4>Flight case " . $flight_case . ":</h4>
      </div>
      <div class=\"divider\">
      </div>
      <div class=\"section\">" .
        "<h5 aling=\"left\">" .
        "Mode value = <u>" . $mode . "</u>. " .
        "Integration method value = <u>" . $integration_method . "</u></br>" .
        "Integration step value = <u>" . $dt . "</u></br>" .
        "Tetta_GS value = <u>" . $Tetta_GS . "</u>. S_GS value = <u>" . $S_GS . "</u>" .
        "</h5>" .
      "</div>
      <div class=\"section\">
        <table width=\"100%\" cellspacing=\"0\" border=\"1\" class=\"highlight\">
          <thead>
            <tr>
              <th>T</th>
              <th>Dv</th>
              <th>Dz</th>
              <th>Fi_st</th>
              <th>Ipsilon</th>
              <th>H</th>
              <th>D_RWY</th>
              <th>Epsilon_gs</th>
              <th>I_gs</th>
            </tr>
          </thead>
          <tbody>";

    for($t; $t <= $tf; $t += $dt) {

      $X[1] = $Y[2]; // pIpsilon
      $X[2] = -1 * $c[1] * $Y[2] - $c[2] * $Y[4] - $c[5] * $X[4] - $c[12] * $Dz - $c[21] * $Fi_st - $c[3] * $Dv; // pWz
      $X[3] = $c[4] * $Y[4] + $c[13] * $Dz + $c[22] * $Fi_st + $c[9] * $Dv; // pTetta
      $X[4] = $Y[1] - $Y[3]; // pAlpha
      $X[5] = $c[6] * $Y[3]; // pH
      $X[6] = $X[5] - $H_set; // pDH
      $n_y = $c[16] * $X[3]; // n_y
      $X[7] = $V0 * cos(deg2rad($Y[3])); // pD_RWY
      $Fi_st = -0.14706 * $Dz;

      switch($mode) {
        case "regulation" : {

          if($Y[7] >= $DGp) {
            
          }
          $X[8] = $k_integral * $Y[6];
          if($X[8] > 10) {
            $X[8] = 10;
          } elseif($X[8] < -10) {
            $X[8] = -10;
          }
          $X[9] = ($k_DH * $X[6] - $Y[9]) / $T_DH;
          $Delta_pre = $Y[8] + $k_H * $Y[6] + $Y[9];
          if($Delta_pre > 10) {
            $Delta_pre = 10;
          } elseif($Delta_pre < -10) {
            $Delta_pre = -10;
          }
          $X[10] = ($Ipsilon_op[1] - $Y[10]) / $T_op[1];
          $Delta = $Delta_pre + $k_Ipsilon * $Y[1] + $Y[10];
          if($Delta > 8) {
            $Delta = 8;
          } elseif($Delta < -8) {
            $Delta = -8;
          }
          $Sigma = $k_Wz * $Y[2] + $Delta;
          if($Sigma > 10) {
            $Sigma = 10;
          } elseif($Sigma < -10) {
            $Sigma = -10;
          }

          if($Delta >= 2) {
            $Delta = 0.6;
          } elseif($Delta <= -2) {
            $Delta = -0.6;
          } else {
            $Delta = 0;
          }
          $X[11] = $Delta; // pHi
          $Dv = $Sigma + $Y[11]; // Dv


        break;
        }
      }
      
      for($t; $t >= $td; $td += $dd){
        echo  "<tr>
        <td>" . number_format($td, 0, '.', ' ') . "</td>
        <td>" . number_format($Dv, 4, '.', ' ') . "</td>
        <td>" . number_format($Dz, 4, '.', ' ') . "</td>
        <td>" . number_format($Fi_st, 4, '.', ' ') . "</td>
        <td>" . number_format($Y[1], 2, '.', ' ') . "</td>
        <td>" . number_format($Y[5], 4, '.', ' ') . "</td>
        <td>" . number_format($Y[7], 4, '.', ' ') . "</td>
        <td>" . number_format($Epsilon_gs, 0, '.', ' ') . "</td>
        <td>" . number_format($I_gs, 0, '.', ' ') . "</td>
        </tr>";
      }

      for($t; $t >= $tg; $tg += $gd){
        array_push($graph_data[$flight_case], ["time" => $td, "H" => $Y[5], "D_RWY" => $Y[7]]);
        if($Y[5] <= 20) {
          break 2;
        }
      }


      switch($integration_method) {
        case "eiler" : {
          for($i = 1; $i <= 17; $i++){
            $Y[$i] += $X[$i] * $dt;
          }
        break;
        }
      }
    }
          echo "</tbody>
        </table><br/>
      </div>";
    $graph_data_file = 'data' . $flight_case . '.json';
    $handle = fopen($graph_data_file, 'w') or die ('Cannot open file: ' . $graph_data_file);
    $graph_content = json_encode($graph_data[$flight_case]);
    fwrite($handle, $graph_content);
      echo "<div class = \"section\">
        <div class=\"chartWithMarkerOverlay\">
          <div id = \"chart_div_fc" . $flight_case . "\" style = \"width: 1000px; height: 500px; margin-left: -100px;\">
          </div>
          <div id = \"chart_div_mp" . $flight_case . "\" class = \"overlay-marker\">
            <img src = \"img/baseline_airplanemode_active_black_48_fliped.png\" class = \"gwd-img-" . $flight_case . $flight_case . $flight_case . $flight_case . " gwd-gen-" . $flight_case . $flight_case . $flight_case . $flight_case . "gwdanimation\"
            data-gwd-motion-path-key = \"gwd-motion-path-" . $flight_case . $flight_case . $flight_case . $flight_case . "\" data-gwd-has-tangent-following = \"\">
          </div>
        </div>
      </div>
    </div>";
  }

  $Vi = $V0 * 3.6 * sqrt(($pr)/(0.1249)); // Vhf
  $M = $Vi / $An;
  echo "<div class=\"container\">
    <div class=\"section\">
      <h4>Vi = " . $Vi . "</h2>
      <h5>M = " . $M . "</h3>
      <h5>Dgp = " . $DGp . "</h3>
    </div>
  </div>";
?>
<html>
<body>
  </main>
  <?php include 'html/footer.html';?>
  <script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
  <script src="js/materialize.js"></script>
  <script src="js/init.js"></script>
  <script type = "text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
      var SSpy_elements = document.querySelectorAll('.scrollspy');
      var SSpy_options = {throttle: 100, scrollOffset: 5, activeClass: "active"};
      var instances = M.ScrollSpy.init(SSpy_elements, SSpy_options);
    });
  </script>
  <script type = "text/javascript">
    function scrollToTop() {
      var graphics = document.getElementById("top-nav");
      graphics.scrollIntoView({block: "start", behavior: "smooth"});
    }
  </script>
  <script type = "text/javascript" src = "https://www.gstatic.com/charts/loader.js">
  </script>
  <script type = "text/javascript">
    google.charts.load('current', {packages: ['corechart','line']});
  </script>
  <script type="text/javascript" src="motionpath_runtime.min.1.0.js" gwd-motionpath-version="1.0">
  </script>
  <script>
    function chart_div_fc1() {
      var chart_data = new google.visualization.DataTable();
      chart_data.addColumn('number', 'flight time');
      chart_data.addColumn('number', 'flight case 1');
      chart_data.addRows([
        <?php
          $data = array();
          $json_data = array();
          for($i = 1; $i <= 6; $i++) {
            $data[$i] = file_get_contents("./data" . $i . ".json");
            $json_data[$i] = json_decode($data[$i], true);
          }
          for ($i = 0; $i <= (count($json_data[1]) - 1); $i++) {
            echo "["
            . $json_data[1][$i]['D_RWY'] . ",  "
            . $json_data[1][$i]['H']
            . "],";
          }
        ?>
      ]);
      var chart_options = {
        animation: {
          startup: true,
          duration: 1000,
          easing: 'out'
        },
        'title' : 'flight case 1',
        curveType: 'function',
        colors: ['orange', 'blue', 'red', 'yellow', 'purple', 'green']
      };
      var chart = new google.visualization.LineChart(document.getElementById('chart_div_fc1'));
      function placeMarker(dataTable) {
        var chart_li = this.getChartLayoutInterface();
        var chart_area = chart_li.getChartAreaBoundingBox();
        document.querySelector('#chart_div_mp1').style.top = Math.floor(chart_li.getYLocation(dataTable.getValue(0, 0))) - 58 + "px";
        document.querySelector('#chart_div_mp1').style.left = Math.floor(chart_li.getXLocation(dataTable.getValue(0, 0))) - 180 + "px";
      };
      google.visualization.events.addListener(chart, 'ready', placeMarker.bind(chart, chart_data));
      chart.draw(chart_data, chart_options);
    }
    google.charts.setOnLoadCallback(chart_div_fc1);

    function chart_div_fc2() {
      var chart_data = new google.visualization.DataTable();
      chart_data.addColumn('number', 'flight time');
      chart_data.addColumn('number', 'flight case 2');
      chart_data.addRows([
        <?php
          $data = array();
          $json_data = array();
          for($i = 1; $i <= 6; $i++) {
            $data[$i] = file_get_contents("./data" . $i . ".json");
            $json_data[$i] = json_decode($data[$i], true);
          }
          for ($i = 0; $i <= (count($json_data[2]) - 1); $i++) {
            echo "["
            . $json_data[2][$i]['D_RWY'] . ",  "
            . $json_data[2][$i]['H']
            . "],";
          }
        ?>
      ]);
      var chart_options = {
        'title' : 'flight case 2',
        curveType: 'function',
        colors: ['orange', 'blue', 'red', 'yellow', 'purple', 'green']
      };
      var chart = new google.visualization.LineChart(document.getElementById('chart_div_fc2'));
      function placeMarker(dataTable) {
        var chart_li = this.getChartLayoutInterface();
        var chart_area = chart_li.getChartAreaBoundingBox();
        document.querySelector('#chart_div_mp2').style.top = Math.floor(chart_li.getYLocation(dataTable.getValue(0, 0))) - 50 + "px";
        document.querySelector('#chart_div_mp2').style.left = Math.floor(chart_li.getXLocation(dataTable.getValue(0, 0))) - 170 + "px";
      };
      google.visualization.events.addListener(chart, 'ready', placeMarker.bind(chart, chart_data));
      chart.draw(chart_data, chart_options);
    }
    google.charts.setOnLoadCallback(chart_div_fc2);

    function chart_div_fc3() {
      var chart_data = new google.visualization.DataTable();
      chart_data.addColumn('number', 'flight time');
      chart_data.addColumn('number', 'flight case 3');
      chart_data.addRows([
        <?php
          $data = array();
          $json_data = array();
          for($i = 1; $i <= 6; $i++) {
            $data[$i] = file_get_contents("./data" . $i . ".json");
            $json_data[$i] = json_decode($data[$i], true);
          }
          for ($i = 0; $i <= (count($json_data[3]) - 1); $i++) {
            echo "["
            . $json_data[3][$i]['D_RWY'] . ",  "
            . $json_data[3][$i]['H']
            . "],";
          }
        ?>
      ]);
      var chart_options = {
        'title' : 'flight case 3',
        curveType: 'function',
        colors: ['orange', 'blue', 'red', 'yellow', 'purple', 'green']
      };
      var chart = new google.visualization.LineChart(document.getElementById('chart_div_fc3'));
      function placeMarker(dataTable) {
        var chart_li = this.getChartLayoutInterface();
        var chart_area = chart_li.getChartAreaBoundingBox();
        document.querySelector('#chart_div_mp3').style.top = Math.floor(chart_li.getYLocation(dataTable.getValue(0, 0))) - 50 + "px";
        document.querySelector('#chart_div_mp3').style.left = Math.floor(chart_li.getXLocation(dataTable.getValue(0, 0))) - 155 + "px";
      };
      google.visualization.events.addListener(chart, 'ready', placeMarker.bind(chart, chart_data));
      chart.draw(chart_data, chart_options);
    }
    google.charts.setOnLoadCallback(chart_div_fc3);

    function chart_div_fc4() {
      var chart_data = new google.visualization.DataTable();
      chart_data.addColumn('number', 'flight time');
      chart_data.addColumn('number', 'flight case 4');
      chart_data.addRows([
        <?php
          $data = array();
          $json_data = array();
          for($i = 1; $i <= 6; $i++) {
            $data[$i] = file_get_contents("./data" . $i . ".json");
            $json_data[$i] = json_decode($data[$i], true);
          }
          for ($i = 0; $i <= (count($json_data[4]) - 1); $i++) {
            echo "["
            . $json_data[4][$i]['D_RWY'] . ",  "
            . $json_data[4][$i]['H']
            . "],";
          }
        ?>
      ]);
      var chart_options = {
        'title' : 'flight case 4',
        curveType: 'function',
        colors: ['orange', 'blue', 'red', 'yellow', 'purple', 'green']
      };
      var chart = new google.visualization.LineChart(document.getElementById('chart_div_fc4'));
      function placeMarker(dataTable) {
        var chart_li = this.getChartLayoutInterface();
        var chart_area = chart_li.getChartAreaBoundingBox();
        document.querySelector('#chart_div_mp4').style.top = Math.floor(chart_li.getYLocation(dataTable.getValue(0, 0))) - 115 + "px";
        document.querySelector('#chart_div_mp4').style.left = Math.floor(chart_li.getXLocation(dataTable.getValue(0, 0))) - 165 + "px";
      };
      google.visualization.events.addListener(chart, 'ready', placeMarker.bind(chart, chart_data));
      chart.draw(chart_data, chart_options);
    }
    google.charts.setOnLoadCallback(chart_div_fc4);

    function chart_div_fc5() {
      var chart_data = new google.visualization.DataTable();
      chart_data.addColumn('number', 'flight time');
      chart_data.addColumn('number', 'flight case 5');
      chart_data.addRows([
        <?php
          $data = array();
          $json_data = array();
          for($i = 1; $i <= 6; $i++) {
            $data[$i] = file_get_contents("./data" . $i . ".json");
            $json_data[$i] = json_decode($data[$i], true);
          }
          for ($i = 0; $i <= (count($json_data[5]) - 1); $i++) {
            echo "["
            . $json_data[5][$i]['D_RWY'] . ",  "
            . $json_data[5][$i]['H']
            . "],";
          }
        ?>
      ]);
      var chart_options = {
        'title' : 'flight case 5',
        curveType: 'function',
        colors: ['orange', 'blue', 'red', 'yellow', 'purple', 'green']
      };
      var chart = new google.visualization.LineChart(document.getElementById('chart_div_fc5'));
      function placeMarker(dataTable) {
        var chart_li = this.getChartLayoutInterface();
        var chart_area = chart_li.getChartAreaBoundingBox();
        document.querySelector('#chart_div_mp5').style.top = Math.floor(chart_li.getYLocation(dataTable.getValue(0, 0))) - 58 + "px";
        document.querySelector('#chart_div_mp5').style.left = Math.floor(chart_li.getXLocation(dataTable.getValue(0, 0))) - 180 + "px";
      };
      google.visualization.events.addListener(chart, 'ready', placeMarker.bind(chart, chart_data));
      chart.draw(chart_data, chart_options);
    }
    google.charts.setOnLoadCallback(chart_div_fc5);

    function chart_div_fc6() {
      var chart_data = new google.visualization.DataTable();
      chart_data.addColumn('number', 'flight time');
      chart_data.addColumn('number', 'flight case 6');
      chart_data.addRows([
        <?php
          $data = array();
          $json_data = array();
          for($i = 1; $i <= 6; $i++) {
            $data[$i] = file_get_contents("./data" . $i . ".json");
            $json_data[$i] = json_decode($data[$i], true);
          }
          for ($i = 0; $i <= (count($json_data[6]) - 1); $i++) {
            echo "["
            . $json_data[6][$i]['D_RWY'] . ",  "
            . $json_data[6][$i]['H']
            . "],";
          }
        ?>
      ]);
      var chart_options = {
        'title' : 'flight case 6',
        curveType: 'function',
        colors: ['orange', 'blue', 'red', 'yellow', 'purple', 'green']
      };
      var chart = new google.visualization.LineChart(document.getElementById('chart_div_fc6'));
      function placeMarker(dataTable) {
        var chart_li = this.getChartLayoutInterface();
        var chart_area = chart_li.getChartAreaBoundingBox();
        document.querySelector('#chart_div_mp6').style.top = Math.floor(chart_li.getYLocation(dataTable.getValue(0, 0))) - 48 + "px";
        document.querySelector('#chart_div_mp6').style.left = Math.floor(chart_li.getXLocation(dataTable.getValue(0, 0))) - 165 + "px";
      };
      google.visualization.events.addListener(chart, 'ready', placeMarker.bind(chart, chart_data));
      chart.draw(chart_data, chart_options);
    }
    google.charts.setOnLoadCallback(chart_div_fc6);
  </script>
</body>
</html>