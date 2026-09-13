-- =============================================================================
-- Atelier du Museau - restauration non destructive du referentiel des races
-- Les lignes existantes sont actualisees par nom ; aucune race n'est supprimee.
-- =============================================================================

SET NAMES utf8mb4;
START TRANSACTION;

INSERT INTO `dog_base`
    (`nom_race`, `poids`, `photo_race`, `description`, `entretien`, `historique`, `caracteristiques`, `astuces_toilettage`)
VALUES
('Berger Allemand', 32.50, 'uploads/breeds/bergerAllemand.png',
 'Chien de travail polyvalent, intelligent et loyal, le Berger Allemand possède une silhouette athlétique et un poil double qui le protège des intempéries. Il a besoin d’activité physique, de stimulation mentale et d’une éducation cohérente.',
 'Brossage une à deux fois par semaine, quotidien pendant les mues. Bain ponctuel avec séchage complet du sous-poil. Contrôle régulier des oreilles, des griffes et des coussinets.',
 'La race a été développée en Allemagne à la fin du XIXe siècle par Max von Stephanitz, qui souhaitait réunir les qualités des chiens de berger régionaux dans un chien de travail stable et polyvalent.',
 'Loyal|Intelligent|Protecteur|Poil double|Mue importante',
 'Débourrer le sous-poil avant le bain, puis utiliser un pulseur après le lavage. Éviter de tondre : le double pelage participe à la protection thermique.'),

('Berger Australien', 23.00, 'uploads/breeds/bergerAustralien.png',
 'Chien de berger dynamique et très proche de son maître, le Berger Australien se distingue par son intelligence, sa vivacité et son pelage mi-long aux couleurs variées. Il convient à une famille active.',
 'Brossage approfondi une à deux fois par semaine et plus fréquent en période de mue. Démêlage attentif derrière les oreilles, aux culottes et sous les pattes.',
 'Malgré son nom, la race s’est principalement développée aux États-Unis auprès des éleveurs et bergers. Elle a accompagné le travail sur les ranchs avant de devenir un chien de sport et de compagnie apprécié.',
 'Énergique|Sociable|Intelligent|Poil mi-long|Sous-poil dense',
 'Démêler avant de mouiller, insister sur les zones de frottement et sécher dans le sens du poil. Une égalisation légère suffit ; la tonte complète est déconseillée.'),

('Bichon Frisé', 5.50, 'uploads/breeds/bichonFrise.png',
 'Petit chien de compagnie joyeux et affectueux, le Bichon Frisé possède un poil blanc, fin et bouclé qui pousse continuellement. Son entretien régulier est indispensable pour éviter les nœuds.',
 'Brossage et peignage plusieurs fois par semaine, nettoyage des yeux et toilettage complet toutes les quatre à six semaines. Le séchage doit redonner du volume sans laisser d’humidité.',
 'Issu de petits chiens méditerranéens proches du barbet, le Bichon Frisé a longtemps accompagné les marins avant de séduire les cours européennes. Sa popularité comme chien de compagnie s’est affirmée au XXe siècle.',
 'Joyeux|Affectueux|Poil bouclé|Faible perte de poils|Entretien soutenu',
 'Travailler sur un poil parfaitement démêlé, laver avec un shampooing adapté au blanc puis sécher en brushing. La coupe aux ciseaux permet de conserver une silhouette ronde.'),

('Bichon Maltais', 4.00, 'uploads/breeds/bichonMaltais.png',
 'Petit chien élégant et vif, le Bichon Maltais porte un poil blanc long, droit et soyeux. Très attaché à sa famille, il demande un entretien minutieux malgré son petit format.',
 'Peignage quotidien si le poil est conservé long, nettoyage fréquent du contour des yeux et de la barbe, bain régulier et coupe des griffes. Une coupe courte facilite l’entretien familial.',
 'Présent depuis l’Antiquité autour du bassin méditerranéen, ce petit chien de compagnie était apprécié dans les foyers aristocratiques. Son nom renvoie à ses origines méditerranéennes plutôt qu’à une certitude géographique unique.',
 'Doux|Vif|Poil long soyeux|Faible perte de poils|Chien de compagnie',
 'Démêler délicatement avec un produit facilitant le peignage. Bien sécher la barbe et le contour des yeux ; éviter les gestes brusques sur ce poil fin et fragile.'),

('Border Collie', 18.00, 'uploads/breeds/borderCollie.png',
 'Chien de berger très intelligent et endurant, le Border Collie a besoin de travail, d’exercice et d’interactions régulières. Son pelage peut être court ou modérément long, avec un sous-poil protecteur.',
 'Brossage hebdomadaire, renforcé pendant les mues. Vérification des oreilles, des franges, des coussinets et retrait des débris après les activités extérieures.',
 'La race s’est développée dans les régions frontalières entre l’Angleterre et l’Écosse pour la conduite précise des troupeaux. La sélection a longtemps privilégié les aptitudes de travail avant l’apparence.',
 'Très intelligent|Endurant|Réactif|Chien de berger|Poil double',
 'Retirer les nœuds des franges avant le bain et éliminer le sous-poil mort au séchage. Préserver le pelage naturel, sans tonte ni coupe excessive.'),

('Bouledogue Français', 12.00, 'uploads/breeds/bouledogueFrancais.png',
 'Petit molosse compact, affectueux et joueur, le Bouledogue Français possède un poil ras facile à entretenir. Sa face courte et ses plis demandent cependant une surveillance régulière.',
 'Brossage doux chaque semaine, nettoyage et séchage soigneux des plis, contrôle des oreilles et des griffes. Utiliser un bain tiède et éviter tout effort ou séchage trop chaud.',
 'La race s’est développée au XIXe siècle à partir de petits bouledogues amenés en France par des ouvriers anglais. Elle est rapidement devenue populaire à Paris comme chien de compagnie.',
 'Affectueux|Joueur|Poil ras|Face brachycéphale|Sensible à la chaleur',
 'Employer des produits doux, nettoyer chaque pli sans laisser d’humidité et limiter le stress. Surveiller constamment la respiration et utiliser une température modérée.'),

('Cairn Terrier', 7.00, 'uploads/breeds/cairnTerrier.png',
 'Petit terrier rustique, éveillé et courageux, le Cairn Terrier possède un poil dur associé à un sous-poil doux. Son aspect naturel et ébouriffé fait partie des caractéristiques de la race.',
 'Brossage hebdomadaire et épilation régulière du poil mort pour préserver sa texture. Bain modéré, contrôle des oreilles, des griffes et du poil entre les coussinets.',
 'Originaire des Highlands et des îles écossaises, il était utilisé pour déloger les nuisibles des amas de pierres appelés cairns. Il compte parmi les plus anciennes races de terriers écossais.',
 'Rustique|Courageux|Indépendant|Poil dur|Sous-poil doux',
 'Privilégier l’épilation manuelle à la tondeuse afin de conserver la couleur et la texture du poil de couverture. Éviter les shampooings trop adoucissants.'),

('Caniche Toy', 4.00, 'uploads/breeds/canicheToy.png',
 'Version miniature du Caniche, le Caniche Toy est intelligent, vif et très proche de son entourage. Son poil bouclé pousse continuellement et permet différentes coupes.',
 'Brossage fréquent, bain et coupe toutes les quatre à six semaines. Nettoyage des oreilles, du contour des yeux et entretien régulier des griffes.',
 'Le Caniche descend de chiens d’eau européens utilisés pour rapporter le gibier. Les variétés de petite taille ont ensuite été sélectionnées comme chiens de compagnie tout en conservant intelligence et facilité d’apprentissage.',
 'Intelligent|Vif|Poil bouclé|Faible perte de poils|Petit format',
 'Démêler entièrement avant le bain, sécher en brushing puis réaliser une coupe adaptée au mode de vie. Manipuler avec soin en raison de son petit gabarit.'),

('Cavalier King Charles', 7.00, 'uploads/breeds/cavalierKingCharles.png',
 'Petit épagneul doux, sociable et élégant, le Cavalier King Charles présente un poil long et soyeux avec des franges. Il apprécie autant les promenades que la vie de famille.',
 'Brossage plusieurs fois par semaine, surtout aux oreilles, aux aisselles et aux franges. Nettoyage régulier des oreilles et séchage complet après le bain.',
 'La race s’inspire des petits épagneuls représentés dans les portraits de la cour anglaise, notamment sous Charles II. Son type actuel a été recréé et fixé au début du XXe siècle.',
 'Doux|Sociable|Poil soyeux|Oreilles longues|Franges abondantes',
 'Démêler les oreilles avec précaution et conserver une finition naturelle. Dégager légèrement les coussinets et éviter de raser le dos ou les franges.'),

('Chihuahua', 2.50, 'uploads/breeds/chihuahua.png',
 'Très petit chien vif et attaché à son maître, le Chihuahua existe à poil court ou à poil long. Malgré son format, il possède un tempérament affirmé et doit être manipulé avec douceur.',
 'Brossage hebdomadaire pour le poil court, plus fréquent pour le poil long. Bain occasionnel, coupe régulière des griffes et surveillance des dents, des yeux et des oreilles.',
 'Considéré comme une race originaire du Mexique, il est souvent rapproché de petits chiens présents dans les civilisations précolombiennes. Le type moderne s’est diffusé à partir de la fin du XIXe siècle.',
 'Très petit|Vif|Attaché|Poil court ou long|Sensible au froid',
 'Utiliser une eau tiède, une table sécurisée et des gestes calmes. Sécher rapidement sans chaleur excessive et porter une attention particulière aux griffes.'),

('Cocker Anglais', 14.00, 'uploads/breeds/cocker.png',
 'Chien joyeux et actif, le Cocker Anglais possède un poil soyeux et de longues oreilles frangées. Son pelage abondant et son goût pour l’extérieur nécessitent un entretien régulier.',
 'Brossage plusieurs fois par semaine, démêlage des oreilles, du poitrail et des franges. Nettoyage fréquent des oreilles et toilettage complet toutes les six à huit semaines.',
 'Le Cocker Anglais appartient à la famille des spaniels britanniques. Il a été sélectionné pour lever le gibier, notamment la bécasse, avant de devenir un chien de compagnie très répandu.',
 'Joyeux|Actif|Poil soyeux|Oreilles tombantes|Franges abondantes',
 'Dégager l’intérieur des oreilles pour favoriser l’aération, retirer le sous-poil mort et conserver des franges naturelles. Bien rincer puis sécher les oreilles.'),

('Fox Terrier', 8.00, 'uploads/breeds/foxTerrier.png',
 'Terrier énergique, curieux et courageux, le Fox Terrier existe à poil lisse ou à poil dur. Il conserve un fort tempérament de chien de chasse et demande une activité régulière.',
 'Pour le poil dur, brossage hebdomadaire et épilation plusieurs fois par an. Pour le poil lisse, brossage régulier au gant. Contrôle des griffes et des oreilles.',
 'Développé en Grande-Bretagne pour accompagner la chasse au renard, il devait pouvoir suivre les chevaux puis poursuivre le gibier dans les terriers. Les variétés à poil lisse et dur ont ensuite été distinguées.',
 'Énergique|Courageux|Curieux|Terrier de chasse|Poil lisse ou dur',
 'Sur un sujet à poil dur, privilégier l’épilation pour préserver texture et couleurs. Adapter la technique au type de poil et ne pas utiliser systématiquement la tondeuse.'),

('Golden Retriever', 30.00, 'uploads/breeds/goldenRetriever.png',
 'Grand chien doux, sociable et coopératif, le Golden Retriever possède un poil double mi-long et imperméable. Il apprécie l’eau, les activités familiales et le travail avec l’humain.',
 'Brossage une à deux fois par semaine, davantage pendant les mues. Démêlage des franges, séchage soigneux après baignade et contrôle régulier des oreilles.',
 'La race a été développée en Écosse au XIXe siècle pour rapporter le gibier sur terre et dans l’eau. Ses qualités de coopération en ont ensuite fait un chien d’assistance et de famille reconnu.',
 'Doux|Sociable|Rapporteur|Poil double|Aime l’eau',
 'Débourrer le sous-poil avant le bain, bien sécher la peau et égaliser seulement les franges et les pieds. La tonte du corps est déconseillée.'),

('Husky Sibérien', 22.00, 'uploads/breeds/huskySiberien.png',
 'Chien nordique endurant et indépendant, le Husky Sibérien possède un double pelage très dense adapté au froid. Sportif et sociable, il a besoin de beaucoup d’activité.',
 'Brossage hebdomadaire et quotidien lors des fortes mues saisonnières. Bain occasionnel et séchage très complet pour éviter que l’humidité reste dans le sous-poil.',
 'Originaire de Sibérie, il a été sélectionné par le peuple tchouktche pour tirer des traîneaux sur de longues distances. Il s’est fait connaître internationalement au début du XXe siècle.',
 'Endurant|Indépendant|Sociable|Double pelage|Forte mue',
 'Utiliser un pulseur pour extraire le sous-poil mort après le bain. Ne jamais tondre un pelage sain : sa repousse peut être irrégulière et sa fonction protectrice altérée.'),

('Labrador Retriever', 30.00, 'uploads/breeds/labradorRetriever.png',
 'Chien équilibré, sociable et polyvalent, le Labrador Retriever possède un poil court, dense et résistant à l’eau. Il est apprécié comme chien de famille, d’assistance et de rapport.',
 'Brossage hebdomadaire, plus fréquent pendant les mues. Bain occasionnel, nettoyage des oreilles et séchage attentif après les activités aquatiques.',
 'Ses ancêtres travaillaient avec les pêcheurs de Terre-Neuve avant d’être importés en Grande-Bretagne au XIXe siècle. La race y a été sélectionnée pour le rapport du gibier.',
 'Sociable|Polyvalent|Gourmand|Poil court dense|Aime l’eau',
 'Retirer le sous-poil mort avec un outil adapté et éviter de raser le pelage. Bien sécher les oreilles et les plis cutanés après le bain.'),

('Lhassa Apso', 7.00, 'uploads/breeds/llhasaApso.png',
 'Petit chien tibétain au poil long, dense et droit, le Lhassa Apso est vigilant, indépendant et attaché à sa famille. Son pelage exige une routine constante lorsqu’il est conservé long.',
 'Brossage et peignage plusieurs fois par semaine, nettoyage des yeux et bain régulier. Une coupe courte peut être entretenue toutes les six à huit semaines.',
 'Originaire du Tibet, il était élevé dans les monastères et les demeures comme chien d’alerte. Son nom est associé à Lhassa, capitale historique du Tibet.',
 'Vigilant|Indépendant|Poil long dense|Petit chien tibétain|Entretien régulier',
 'Séparer le poil par zones et démêler des pointes vers la racine. Sécher parfaitement en brushing ; dégager les yeux et les coussinets sans altérer la silhouette.'),

('Pomsky', 10.00, 'uploads/breeds/pomsky.png',
 'Chien issu du croisement entre Husky Sibérien et Spitz, le Pomsky présente une taille et un tempérament variables. Son double pelage dense demande un entretien suivi.',
 'Brossage plusieurs fois par semaine et quotidien en période de mue. Bain modéré, démêlage du sous-poil et séchage complet jusqu’à la peau.',
 'Le Pomsky est un croisement récent développé principalement en Amérique du Nord. Il ne constitue pas une race reconnue par toutes les organisations cynologiques et son apparence peut varier selon les lignées.',
 'Vif|Double pelage|Taille variable|Sociable|Mue saisonnière',
 'Débourrer et sécher au pulseur sans tondre le corps. Adapter la séance à la densité réelle du pelage, très variable d’un individu à l’autre.'),

('Schnauzer', 15.00, 'uploads/breeds/schnauzer.png',
 'Chien robuste et vigilant, le Schnauzer se reconnaît à ses sourcils et à sa barbe. Son poil dur est doublé d’un sous-poil et nécessite une technique adaptée pour conserver sa texture.',
 'Brossage hebdomadaire, entretien fréquent de la barbe et épilation ou coupe toutes les six à huit semaines. Nettoyage de la barbe après les repas et séchage soigneux.',
 'Originaire d’Allemagne, le Schnauzer était un chien de ferme polyvalent, utilisé pour garder les biens et chasser les nuisibles. Plusieurs tailles ont ensuite été développées.',
 'Vigilant|Robuste|Poil dur|Barbe caractéristique|Chien polyvalent',
 'L’épilation manuelle conserve mieux la texture et la couleur du poil dur. Nettoyer et sécher soigneusement la barbe, puis structurer sourcils et garnitures.'),

('Shih Tzu', 6.00, 'uploads/breeds/shiTzu.png',
 'Petit chien de compagnie sociable, le Shih Tzu possède un poil long et dense ainsi qu’une face courte. Il apprécie la proximité humaine et nécessite un entretien régulier.',
 'Brossage quotidien pour un poil long, nettoyage des yeux et de la face, bain régulier et coupe toutes les quatre à six semaines si le poil est raccourci.',
 'Développé au Tibet puis en Chine impériale, le Shih Tzu a été élevé comme chien de compagnie dans les palais. Son nom signifie traditionnellement « chien lion ».',
 'Sociable|Poil long dense|Face courte|Chien de compagnie|Entretien soutenu',
 'Démêler doucement, garder les plis de la face propres et secs, et surveiller la respiration pendant la séance. Une coupe courte facilite l’entretien quotidien.'),

('Shiba Inu', 9.50, 'uploads/breeds/shibaInu.png',
 'Chien japonais vif, propre et indépendant, le Shiba Inu possède un poil de couverture droit et un sous-poil dense. Il conserve un aspect naturel qui demande peu de coupe.',
 'Brossage hebdomadaire, quotidien pendant les mues importantes. Bain occasionnel, contrôle des griffes et séchage complet du sous-poil.',
 'Le Shiba Inu est une ancienne race japonaise utilisée pour la chasse au petit gibier dans les régions montagneuses. Un programme de préservation a permis de fixer le type moderne au XXe siècle.',
 'Indépendant|Vif|Propre|Double pelage|Race japonaise',
 'Procéder avec calme, car certains sujets apprécient peu les manipulations. Extraire le sous-poil mort au pulseur et ne pas tondre le pelage.'),

('Spitz Nain', 3.00, 'uploads/breeds/spitzNain.png',
 'Très petit chien vif et expressif, le Spitz Nain possède un double pelage abondant formant une collerette. Son sous-poil dense soutient le poil de couverture.',
 'Brossage soigneux plusieurs fois par semaine jusqu’à la peau, bain régulier et séchage complet. Surveillance des nœuds derrière les oreilles et aux zones de frottement.',
 'Le Spitz Nain, aussi appelé Poméranien, descend des chiens de type spitz européens. Sa taille a été progressivement réduite, notamment en Grande-Bretagne au XIXe siècle.',
 'Vif|Petit format|Double pelage|Collerette abondante|Mue saisonnière',
 'Brosser par couches, sécher à rebrousse-poil pour redonner du volume et égaliser légèrement la silhouette. Éviter toute tonte courte susceptible d’altérer la repousse.'),

('Teckel', 8.00, 'uploads/breeds/teckel.png',
 'Chien au corps allongé, courageux et curieux, le Teckel existe en plusieurs tailles et trois types de poil : ras, long ou dur. L’entretien dépend de sa variété.',
 'Poil ras : brossage doux. Poil long : démêlage régulier des franges. Poil dur : épilation périodique. Pour tous, contrôle des griffes et des oreilles.',
 'Développé en Allemagne pour la chasse sous terre, notamment au blaireau, le Teckel devait allier courage, flair et morphologie adaptée aux terriers.',
 'Courageux|Curieux|Corps allongé|Trois types de poil|Chien de chasse',
 'Identifier le type de poil avant de choisir la technique. Manipuler le dos avec précaution, soutenir correctement le corps et utiliser l’épilation pour le poil dur.'),

('Yorkshire Terrier', 3.20, 'uploads/breeds/yorkshireTerrier.png',
 'Petit terrier vif au poil long, fin et soyeux, le Yorkshire Terrier est aujourd’hui un chien de compagnie populaire. Son pelage ressemble davantage à des cheveux qu’à un poil laineux.',
 'Brossage fréquent, bain régulier, nettoyage des yeux et entretien des oreilles. Une coupe courte toutes les six à huit semaines simplifie les soins quotidiens.',
 'La race s’est développée au XIXe siècle dans le nord de l’Angleterre à partir de différents petits terriers utilisés contre les nuisibles. Elle a ensuite été sélectionnée pour son format et son poil élégant.',
 'Vif|Affectueux|Poil fin soyeux|Petit terrier|Faible perte de poils',
 'Démêler avec douceur et protéger le poil fin de la casse. Dégager les oreilles, les yeux et les coussinets, puis choisir une coupe compatible avec le rythme d’entretien du foyer.')
ON DUPLICATE KEY UPDATE
    `poids` = VALUES(`poids`),
    `photo_race` = VALUES(`photo_race`),
    `description` = VALUES(`description`),
    `entretien` = VALUES(`entretien`),
    `historique` = VALUES(`historique`),
    `caracteristiques` = VALUES(`caracteristiques`),
    `astuces_toilettage` = VALUES(`astuces_toilettage`);

COMMIT;
