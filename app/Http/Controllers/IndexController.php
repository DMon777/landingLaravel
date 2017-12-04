<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Page;
use App\Service;
use App\Portfolio;
use App\People;

use App\Mail\UserMessage;

use DB;
use Mail;

class IndexController extends Controller
{

    public function execute(Request $request)
    {

        if($request->isMethod('post')) {

            $messages = [

                'required' => "Поле :attribute обязательно к заполнению",
                'email' => "Поле :attribute должно соответствовать email адресу"

            ];

            $this->validate($request,[

                'name' => 'required|max:255',
                'email' => 'required|email',
                'text' => 'required'

            ], $messages);

            $data = $request->all();

            $result = Mail::send('site.email',['data'=>$data], function($message) use ($data) {


                $mail_admin = env('MAIL_ADMIN');

                $message->from($data['email'],$data['name']);
                $message->to($mail_admin,'Mr. Admin')->subject('Question');
            });


            if($result) {
                return redirect()->route('home')->with('status', 'Email is send');
            }

            //Mail::to(env("MAIL_ADMIN"))->send(new UserMessage($data));


        }

        $pages = Page::all();
        $portfolios = Portfolio::get(array('name','filter','images'));
        $services = Service::where('id','<',20)->get();
        $peoples = People::take(3)->get();

        $menu = array();
        foreach($pages as $page) {
            $item = array('title' =>$page->name,'alias'=>$page->alias);
            array_push($menu,$item);
        }

        $static_menu_items = [
            ['title'=>'Services','alias'=>'service'],
            ['title'=>'Portfolio','alias'=>'Portfolio'],
            ['title'=>'Team','alias'=>'team'],
            ['title'=>'Contact','alias'=>'contact']
        ];

        foreach ($static_menu_items as $val) {
            array_push($menu,$val);
        }

        $tags = DB::table('portfolios')->distinct()->select('filter')->get();

        return view('site.index',[

            'menu'=> $menu,
            'pages' => $pages,
            'services' => $services,
            'portfolios' => $portfolios,
            'peoples' => $peoples,
            'tags' => $tags,

        ]);
    }

}
