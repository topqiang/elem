<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * Class IndexController
 * @package Admin\Controller
 */
class IndexController extends Controller{
    public $basic;
	public function _initialize(){
		if(empty($_SESSION['aid']))redirect(U('Manager/Login'));
        $this->basic=D('Basic');
	}
    public function index(){
        //判断登陆
        $this->display('index');
    }
    public function top(){
        //判断登陆
        $this->display('top');
    }
    public function left(){
        //判断登陆
        $this->display('left');
    }
    /**
     * 统计
     */
    public function main(){
        //日期为空 获取当前日期
        if(empty($_POST['year']) && empty($_POST['month'])){
            $year = date('Y');
            $month = date('m');
            $this->createYearSelect($year);//年份下拉
        }else{
            $year = $_POST['year'];
            $month = $_POST['month'];
            $this->createYearSelect($year);
        }
        $day_num = getDayNum($year,$month);        //获得天数
        $x_date = $this->createX($day_num);        //横坐标
        $line = $this->createLine($this->getData($day_num,$year,$month));        //折线
        $this->createMonthSelect();        //月份下拉
        //模板赋值
        $this->assign('date_flag',$year.'年'.$month.'月');
        $this->assign('x_date',$x_date);
        $this->assign('line',$line);
        $where['type'] = 2;
        $where['status'] = 9;

        $time_start = strtotime(date($year.'-'.$month));//今天的日期
        $time_end = strtotime(date($year.'-'.$month)) + date("t",strtotime("$year-$month"))*24*3600;//得到明天的日期

        $where['ctime'] =array(array('egt',$time_start),array('lt',$time_end),'and');

        $order = $this->basic->basicList('Order',$where,'','');
        //echo $this->basic->_sql();
        $price = 0;
        foreach($order as $value){
            $list_arr=unserialize($value['list']);
            $obj=D('Food');
            foreach($list_arr as $v){
                $info = $obj->where(array('fid'=>$v['id']))->find();
                $price = $price+$info['price']*$v['num'];
            }
        }

        $this->assign('count',$price);
        if($_SESSION['g_id']!=3){
            $this->display('main');}
        }

    /**
     *创建横坐标
     */
    public function createX($day_num){
        //创建横坐标显示
        $date = '';
        for($i=1;$i<=$day_num;$i++){
            $date.= "'$i".号."',";
        }
        $x_date = substr($date,0,strlen($date)-1);
        return $x_date;
    }
    /**
     *获取统计数据
     */
    public function getData($day_num,$year,$month){
        //按天获取统计数据
        for($i=1;$i<=$day_num;$i++){
            $price = 0;
            $start_time = strtotime("$year-$month-$i 00:00:00");
            $end_time = strtotime("$year-$month-$i 23:59:59");
            //获取统计数据
            $where['ctime'] = array(array('gt',$start_time),array('lt',$end_time));
            $where['type'] = 2;
            $where['status'] = 9;
            $order = $this->basic->basicList('Order',$where,'','');
            foreach($order as $value){
                $list_arr=unserialize($value['list']);
                $obj=D('Food');
                foreach($list_arr as $v){
                    $info=$obj->where(array('fid'=>$v['id']))->find();
                    $price=$price+$info['price']*$v['num'];
                }
            }
            $data[] = $price;
        }
        //为数据添加标题
        $result["每日营业额"] = $data;
        return $result;
    }
    /**
     *创建折线
     */
    public function createLine($result){
        //创建折线参数
        $line = '';
        foreach($result as $key=>$value){
            $line_data = '';
            $line.= "{name: '".$key."',data:[";
            foreach($value as $v){
                $line_data.=$v.',';
            }
            $line.=substr($line_data,0,strlen($line_data)-1);
            $line.="]},";
        }
        $line = substr($line,0,strlen($line)-1);
        return $line;
    }
    /**
     *创建年份下拉菜单
     */
    public function createYearSelect($year){
        //创建年份的下拉菜单
        $year_sel = "<select name='year' class='time_sel'>";
        for($j=14;$j>=0;$j--){
            if(intval($year) == (intval(date('Y'))-$j)){
                $year_sel.="<option value='".(intval(date('Y'))-$j)."' selected='true'>".(intval(date('Y'))-$j)."</option>";
            }else{
                $year_sel.="<option value='".(intval(date('Y'))-$j)."'>".(intval(date('Y'))-$j)."</option>";
            }
        }
        $year_sel.= "</select>";
        $this->assign('year_sel',$year_sel);
    }

    /**
     * 创建月份下拉菜单
     */
    public function createMonthSelect(){
        $month_sel = "<select name='month' class='month_sel time_sel'>";
        $month_sel.="<option value='0'>--选择月份--</option>";
        for($i=1;$i<=12;$i++){
            if($i<10){
                $month_sel.="<option value='".('0'.$i)."'>".('0'.$i)."</option>";
            }else{
                $month_sel.="<option value='".$i."'>".$i."</option>";
            }
        }
        $month_sel.="</select>";
        $this->assign('month_sel',$month_sel);
    }


}
