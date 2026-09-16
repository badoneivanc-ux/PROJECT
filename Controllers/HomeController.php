<?php

namespace Project\Controllers;

class HomeController extends Controller
{
    public function index(): void
    {
        $this->render('home/index', [
            'pageTitle' => "Toilettage canin à domicile à Vincennes | L'Atelier du Museau",
            'pageDescription' => "L'Atelier du Museau propose un service de toilettage canin à domicile à Vincennes : bain, coupe, séchage et soin des griffes.",
            'canonicalUrl' => 'https://latelierdumuseau.fr/',
            'structuredData' => [
                '@context' => 'https://schema.org',
                '@type' => 'LocalBusiness',
                'name' => "L'Atelier du Museau",
                'url' => 'https://latelierdumuseau.fr/',
                'image' => 'https://latelierdumuseau.fr/logo.jpg',
                'telephone' => '+33185135985',
                'description' => 'Service de toilettage canin à domicile à Vincennes.',
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => 'Vincennes',
                    'postalCode' => '94300',
                    'addressCountry' => 'FR',
                ],
                'areaServed' => 'Vincennes',
            ],
        ]);
    }

    public function tarifs(): void
    {
        $this->render('home/tarifs', [
            'pageTitle' => "Tarifs de toilettage canin à Vincennes | L'Atelier du Museau",
            'pageDescription' => "Consultez les tarifs de toilettage canin à domicile à Vincennes selon la taille, le poil et les besoins de votre chien.",
            'canonicalUrl' => 'https://latelierdumuseau.fr/index.php?controller=home&action=tarifs',
        ]);
    }

    public function mentionsLegales(): void
    {
        $this->render('home/mentions-legales', [
            'pageTitle' => "Mentions légales | L'Atelier du Museau",
            'pageDescription' => "Consultez les mentions légales du site L'Atelier du Museau, service de toilettage canin à domicile à Vincennes.",
            'canonicalUrl' => 'https://latelierdumuseau.fr/index.php?controller=home&action=mentionsLegales',
        ]);
    }

    public function cgv(): void
    {
        $this->render('home/cgv', [
            'pageTitle' => "Conditions générales de vente | L'Atelier du Museau",
            'pageDescription' => "Consultez les conditions générales de vente des prestations de toilettage canin à domicile de L'Atelier du Museau.",
            'canonicalUrl' => 'https://latelierdumuseau.fr/index.php?controller=home&action=cgv',
        ]);
    }

    public function confidentialite(): void
    {
        $this->render('home/confidentialite', [
            'pageTitle' => "Politique de confidentialité | L'Atelier du Museau",
            'pageDescription' => "Découvrez comment L'Atelier du Museau collecte, utilise et protège vos données personnelles conformément au RGPD.",
            'canonicalUrl' => 'https://latelierdumuseau.fr/index.php?controller=home&action=confidentialite',
        ]);
    }
}