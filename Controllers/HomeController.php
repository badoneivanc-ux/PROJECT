<?php

namespace Project\Controllers;

class HomeController extends Controller
{
    public function index(): void
    {
        $this->render('home/index');
    }

    public function tarifs(): void
    {
        $this->render('home/tarifs');
    }
}