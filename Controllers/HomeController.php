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

    public function mentionsLegales(): void
    {
        $this->render('home/mentions-legales');
    }

    public function cgv(): void
    {
        $this->render('home/cgv');
    }

    public function confidentialite(): void
    {
        $this->render('home/confidentialite');
    }
}