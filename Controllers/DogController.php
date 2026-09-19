<?php

namespace Project\Controllers;

use Project\Models\DogModel;

class DogController extends Controller
{
    private const MAX_PHOTO_SIZE = 5 * 1024 * 1024;

    private DogModel $dogModel;

    public function __construct()
    {
        $this->dogModel = new DogModel();
    }

    /**
     * Liste toutes les races disponibles (vitrine publique).
     */
    public function index(): void
    {
        $breeds = $this->dogModel->getAllBreeds();
        $this->render('dog/index', [
            'breeds' => $breeds,
            'pageTitle' => "Races de chiens toilettées à Vincennes | L'Atelier du Museau",
            'pageDescription' => "Découvrez les races de chiens prises en charge, leurs besoins d'entretien et les conseils de toilettage de L'Atelier du Museau.",
            'canonicalUrl' => 'https://latelierdumuseau.fr/index.php?controller=dog&action=index',
        ]);
    }

    /**
     * Affiche le détail d'une race.
     */
    public function show(): void
    {
        $id    = (int)($_GET['id'] ?? 0);
        $breed = $id ? $this->dogModel->getBreedById($id) : null;

        if (!$breed) {
            $this->setFlash('error', 'Race introuvable.');
            $this->redirect('/index.php?controller=dog&action=index');
        }

        $this->render('dog/show', [
            'breed' => $breed,
            'pageTitle' => "Toilettage {$breed->nomRace} à Vincennes | L'Atelier du Museau",
            'pageDescription' => "Découvrez les besoins d'entretien et les conseils de toilettage pour un {$breed->nomRace} avec L'Atelier du Museau à Vincennes.",
            'canonicalUrl' => "https://latelierdumuseau.fr/index.php?controller=dog&action=show&id={$breed->id}",
        ]);
    }

    /**
     * Formulaire + traitement de modification d'un chien par son propriétaire.
     */
    public function editDog(): void
    {
        $this->requireUser();

        $id  = (int)($_GET['id'] ?? 0);
        $dog = $id ? $this->dogModel->getDogById($id) : null;

        if (!$dog || $dog->idUserFk !== (int)$_SESSION['user_id']) {
            $this->setFlash('error', 'Chien introuvable.');
            $this->redirect('/index.php?controller=user&action=profile');
        }

        $breeds = $this->dogModel->getAllBreeds();

        if ($this->isPost()) {
            $this->verifyCsrfToken($_POST['csrf_token'] ?? '');

            $nom      = trim($_POST['nom'] ?? '');
            $breedId  = (int)($_POST['id_race_FK'] ?? 0);
            $poids    = (float)($_POST['poids'] ?? 0);
            $age      = (float)($_POST['age'] ?? 0);
            $sexe     = (int)($_POST['sexe'] ?? 0);

            $nom_race = '';
            if ($breedId > 0) {
                $breed    = $this->dogModel->getBreedById($breedId);
                $nom_race = $breed ? $breed->nomRace : '';
            }
            if (empty($nom_race)) {
                $nom_race = trim($_POST['nom_race_custom'] ?? '');
            }

            if (empty($nom) || empty($nom_race) || $poids <= 0 || $age <= 0) {
                $this->setFlash('error', 'Tous les champs obligatoires doivent être renseignés.');
                $this->render('user/dog_form', ['breeds' => $breeds, 'dog' => $dog]);
                return;
            }

            $photo = $dog->photoChien;
            if (!empty($_FILES['photo_chien']['name'])) {
                $newPhoto = $this->handlePhotoUpload($_FILES['photo_chien']);
                if ($newPhoto === null) {
                    $this->setFlash('error', 'La photo doit être une image JPEG, PNG ou WebP valide.');
                    $this->render('user/dog_form', ['breeds' => $breeds, 'dog' => $dog]);
                    return;
                }
                $photo = $newPhoto;
            }

            if ($this->dogModel->updateDog($id, $nom, $nom_race, $poids, $age, $sexe, $photo)) {
                $this->setFlash('success', 'Chien modifié avec succès.');
                $this->redirect('/index.php?controller=user&action=profile');
            } else {
                $this->setFlash('error', 'Erreur lors de la modification.');
                $this->render('user/dog_form', ['breeds' => $breeds, 'dog' => $dog]);
            }
        } else {
            $this->render('user/dog_form', ['breeds' => $breeds, 'dog' => $dog]);
        }
    }

    /**
     * Formulaire + traitement d'ajout d'un chien par un utilisateur connecté.
     * Le chien est automatiquement associé à l'utilisateur en session.
     */
    public function addDog(): void
    {
        $this->requireUser();

        $breeds = $this->dogModel->getAllBreeds();

        if ($this->isPost()) {
            $this->verifyCsrfToken($_POST['csrf_token'] ?? '');

            $nom      = trim($_POST['nom'] ?? '');
            $breedId  = (int)($_POST['id_race_FK'] ?? 0);
            $poids    = (float)($_POST['poids'] ?? 0);
            $age      = (float)($_POST['age'] ?? 0);
            $sexe     = (int)($_POST['sexe'] ?? 0);

            // Résolution du nom de race depuis le référentiel
            $nom_race = '';
            if ($breedId > 0) {
                $breed    = $this->dogModel->getBreedById($breedId);
                $nom_race = $breed ? $breed->nomRace : '';
            }
            // Fallback : race saisie manuellement
            if (empty($nom_race)) {
                $nom_race = trim($_POST['nom_race_custom'] ?? '');
            }

            if (empty($nom) || empty($nom_race) || $poids <= 0 || $age <= 0) {
                $this->setFlash('error', 'Tous les champs obligatoires doivent être renseignés.');
                $this->render('user/dog_form', ['breeds' => $breeds]);
                return;
            }

            $photo = $this->handlePhotoUpload($_FILES['photo_chien'] ?? []);
            if ($photo === null) {
                $this->setFlash('error', 'La photo doit être une image JPEG, PNG ou WebP valide.');
                $this->render('user/dog_form', ['breeds' => $breeds]);
                return;
            }

            $dogId = $this->dogModel->createDog($nom, $nom_race, $poids, $age, $sexe, $photo, $_SESSION['user_id']);

            if ($dogId) {
                // Lier à la race du référentiel si sélectionnée
                if ($breedId > 0) {
                    $this->dogModel->linkDogToBreed($dogId, $breedId);
                }
                $this->setFlash('success', 'Votre chien a bien été ajouté !');
            } else {
                $this->setFlash('error', 'Erreur lors de l\'ajout du chien.');
            }

            $this->redirect('/index.php?controller=user&action=profile');
        } else {
            $this->render('user/dog_form', ['breeds' => $breeds]);
        }
    }

    /**
     * Formulaire + traitement de création d'une race dans le référentiel (admin).
     */
    public function createBreed(): void
    {
        $this->requireAdmin();

        if ($this->isPost()) {
            $this->verifyCsrfToken($_POST['csrf_token'] ?? '');

            $nom_race  = trim($_POST['nom_race'] ?? '');
            $poids     = (float)($_POST['poids'] ?? 0);
            $desc      = trim($_POST['description'] ?? '');
            $entretien = trim($_POST['entretien'] ?? '');
            $historique        = trim($_POST['historique'] ?? '');
            $caracteristiques  = trim($_POST['caracteristiques'] ?? '');
            $astuces           = trim($_POST['astuces_toilettage'] ?? '');

            if (empty($nom_race) || empty($desc) || empty($entretien)) {
                $this->setFlash('error', 'Le nom, la description et l\'entretien sont obligatoires.');
                $this->render('admin/breed_form', ['breed' => null]);
                return;
            }

            $photo_race = $this->handlePhotoUpload($_FILES['photo_race'] ?? [], 'breeds');
            if ($photo_race === null) {
                $this->setFlash('error', 'La photo doit être une image JPEG, PNG ou WebP valide.');
                $this->render('admin/breed_form', ['breed' => null]);
                return;
            }

            $id = $this->dogModel->createBreed($nom_race, $poids, $desc, $entretien, $historique, $caracteristiques, $astuces, $photo_race);
            if ($id) {
                $this->setFlash('success', 'Race ajoutée avec succès.');
                $this->redirect('/index.php?controller=admin&action=breeds');
            } else {
                $this->setFlash('error', 'Erreur lors de l\'ajout.');
                $this->render('admin/breed_form', ['breed' => null]);
            }
        } else {
            $this->render('admin/breed_form', ['breed' => null]);
        }
    }

    /**
     * Formulaire + traitement de modification d'une race du référentiel (admin).
     */
    public function editBreed(): void
    {
        $this->requireAdmin();

        $id    = (int)($_GET['id'] ?? 0);
        $breed = $id ? $this->dogModel->getBreedById($id) : null;

        if (!$breed) {
            $this->setFlash('error', 'Race introuvable.');
            $this->redirect('/index.php?controller=admin&action=breeds');
        }

        if ($this->isPost()) {
            $this->verifyCsrfToken($_POST['csrf_token'] ?? '');

            $nom_race  = trim($_POST['nom_race'] ?? '');
            $poids     = (float)($_POST['poids'] ?? 0);
            $desc      = trim($_POST['description'] ?? '');
            $entretien = trim($_POST['entretien'] ?? '');
            $historique        = trim($_POST['historique'] ?? '');
            $caracteristiques  = trim($_POST['caracteristiques'] ?? '');
            $astuces           = trim($_POST['astuces_toilettage'] ?? '');

            if (empty($nom_race) || empty($desc) || empty($entretien)) {
                $this->setFlash('error', 'Le nom, la description et l\'entretien sont obligatoires.');
                $this->render('admin/breed_form', ['breed' => $breed]);
                return;
            }

            $photo_race = $this->handlePhotoUpload($_FILES['photo_race'] ?? [], 'breeds');
            if ($photo_race === null) {
                $this->setFlash('error', 'La photo doit être une image JPEG, PNG ou WebP valide.');
                $this->render('admin/breed_form', ['breed' => $breed]);
                return;
            }

            if ($this->dogModel->updateBreed($id, $nom_race, $poids, $desc, $entretien, $historique, $caracteristiques, $astuces, $photo_race)) {
                $this->setFlash('success', 'Race modifiée avec succès.');
                $this->redirect('/index.php?controller=admin&action=breeds');
            } else {
                $this->setFlash('error', 'Erreur lors de la modification.');
                $this->render('admin/breed_form', ['breed' => $breed]);
            }
        } else {
            $this->render('admin/breed_form', ['breed' => $breed]);
        }
    }

    /**
     * Suppression d'une race du référentiel (admin, via POST).
     */
    public function deleteBreed(): void
    {
        $this->requireAdmin();
        $this->verifyCsrfToken($_POST['csrf_token'] ?? '');

        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $this->dogModel->deleteBreed($id);
            $this->setFlash('success', 'Race supprimée avec succès.');
        }

        $this->redirect('/index.php?controller=admin&action=breeds');
    }

    /**
     * Suppression d'un chien (admin, via POST).
     */
    public function delete(): void
    {
        $this->requireAdmin();
        $this->verifyCsrfToken($_POST['csrf_token'] ?? '');

        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $this->dogModel->deleteDog($id);
            $this->setFlash('success', 'Chien supprimé avec succès.');
        }

        $this->redirect('/index.php?controller=admin&action=dogs');
    }

    /**
     * Gère l'upload d'une photo et retourne le chemin relatif.
     * $subdir : 'dogs' ou 'breeds'
     */
    private function handlePhotoUpload(array $file, string $subdir = 'dogs'): ?string
    {
        $uploadError = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($uploadError === UPLOAD_ERR_NO_FILE) {
            return '';
        }

        if ($uploadError !== UPLOAD_ERR_OK
            || empty($file['tmp_name'])
            || ($file['size'] ?? 0) > self::MAX_PHOTO_SIZE) {
            return null;
        }

        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];
        $finfo        = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType     = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset($allowedTypes[$mimeType])) {
            return null;
        }

        $uploadDir = __DIR__ . '/../public/uploads/' . $subdir . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mimeType];
        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            return null;
        }

        return 'uploads/' . $subdir . '/' . $filename;
    }
}