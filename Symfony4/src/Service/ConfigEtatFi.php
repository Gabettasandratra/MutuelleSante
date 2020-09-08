<?php

namespace App\Service;

class ConfigEtatFi {
    public function actifNonCourant()
    {
        // [niveau, libellé, brut, amort/perte]
        return array(
                    [0, 'Ecart d’acquisition (ou goodwill)', [206, 207] , [2906, 2807]],
                    [0, 'Immobilisations incorporelles'],
                        [1, 'Frais de développement immobilier', [203] , [2803]],
                        [1, 'Concession, brevets, licences, logiciels et valeurs similaires', [204, 205], [2804, 2805]],
                        [1, 'Autres', [208] , [2808]],
                    [0, 'Immobilisations corporelles'],
                        [1, 'Terrains',[ 211, 212] , [2811, 2812]],
                        [1, 'Construction', [213] , [2813, 2913]],
                        [1, 'Installation technique', [215], [2815, 2915]],
                        [1, 'Autres', [218] , [2818, 2918]],
                    [0, 'Immobilisation mis en concession', [22] ,[282 - 292]],
                    [0, 'Immobilisations en cours', [232, 237, 238], [2932, 2937, 2938]],
                    [0, 'Immobilisations financières'],
                        [1, 'Titres mis en équivalence', [261, 262, 265] , [2961, 2962, 2965]],
                        [1, 'Autres participations et créances rattachées', [266, 267, 268, 269], [2966, 2967, 2968]],
                        [1, 'Autres titres immobilisés', [271, 272, 273], [2971, 2972, 2973]],
                        [1, 'Prêts et autres immobilisations financières', [274, 275, 276, 277, 279], [2974, 2975, 2976, 2977, 2979]],
                    [0, 'Impôts différés actifs – non courants', [133], []]
        );
        
    }

    public function actifCourant()
    {
        // [niveau, libellé, brut, amort/perte]
        return array(
                    [0, 'Stocks et en cours'],
                        [1, 'Matière première', [31, 32], [391, 392]],
                        [1, 'En cours de production', [33, 34], [393, 394]],
                        [1, 'Produits finis', [35], [395]],
                        [1, 'Marchandises', [37], [397]],
                        [1, 'A l’extérieur', [38], [398]],
                    [0, 'Créances et emplois assimilés'],
                        [1, 'Clients et autres débiteurs', [409,411,413,416,417,418] ,[491]],
                        [1, 'Autres créances et actifs assimilés', [422,425,4287,441,442,443,445,4487,451,456,458,462,465,467,4687,486], [495,496]],
                    [0, 'Trésorerie et équivalents de trésorerie'],
                        [1, 'Placements et autres équivalents de trésorerie',[50], [59]],
                        [1, 'Trésorerie (fonds en caisse et dépôts à vue)', [511,512,515,517,5187,52,53,54], []]
        );
        
    }

    public function capitauxPropres()
    {
        // [niveau, libellé, brut, amort/perte]
        return array(
            [0, 'Capital émis', [101,108], [109]],
            [0, 'Primes et réserves consolidées', [104,106], []],
            [0, 'Ecarts d’évaluation', [105], []],
            [0, 'Ecart d’équivalence', [107], []],
            [0, 'Résultat net – part du groupe', [120], [129]],
            [0, 'Autres capitaux propres – report à nouveau', [110], [119]]
        );
        
    }

    public function passifNonCourant()
    {
        // [niveau, libellé, brut, amort/perte]
        return array(
            [0,'Produits différés : subventions d’investissement', [131,132], []],
            [0,'Impôts différés', [134,138], []],
            [0,'Emprunts et dettes financières', [16,17], []],
            [0,'Provisions et produits constatés d’avance', [15], []]
        );
        
    }

    public function passifCourant()
    {
        // [niveau, libellé, brut, amort/perte]
        return array(
            [0, 'Dettes court terme – partie court terme de dettes long terme', [516,16], []],
            [0, 'Fournisseurs et comptes rattachés', [401,403,408,419,421,422,426,427,4286,431,432,438,444,445, 446,447,4486,451,455,456,457,458,464,467,4686], []],
            [0, 'Provisions et produits constatés d’avance – passifs courants', [481,487], []],
            [0, 'Autres dettes', [404,405], []],
            [0, 'Comptes de trésorerie (découverts bancaires)', [519], []]
        );
        
    }

    /**
     * COMPTE DE RESULTAT MODELE FRANCAIS
     *  - les élements soustractifs sont directement calculé à l'interieur
     */
    public function chiffreAffaireNet()
    {
        // [niveau, libellé, brut, amort/perte]
        return array(
            [0,'Ventes de marchandises',[707,7097]],
            [0,'Production vendue'],
            [1,'Biens',[701,702,703,7091,7092]],
            [1,'Services',[704,705,706,708,7094,7095,7096,7098]],
        );
    }

    public function productionExploitation()
    {
        // [niveau, libellé, brut, amort/perte]
        return array(
            [0,'Production stockée', [7133,7134,7135]],
            [0,'Production immobilisée',[72,73]],
            [0,"Subvention d'exploitation",[74]],
            [0,"Reprises dépreciations et amortissements",[781]],
            [0,"Transferts de charges",[791]],
            [0,"Autres produits",[751,752,753,758]]
        );
    }

    /**
     * Charge d'exploitation
     */
    public function chargeExploitation()
    {
        // [niveau, libellé, brut, amort/perte]
        return array(
            [0,"Achats de marchandises", [607,6087,6097]],
            [0,"Variation de stock (marchandises)",[6037]],
            [0,"Achats de matières premières et autres approvisionnements",[601,602,6081,6091,6092]],
            [0,"Variation de stock (matières premières et approvisionnements)",[6031,6032]],
            [0,"Autres achats et charges externes",[604,605,606,6094,6095,6096,6098,61,62]],
            [0,"Impôts, taxes et versements assimilés",[631,633,635,637]],
            [0,"Salaires et traitements",[641,644]],
            [0,"Charges sociales",[645,646,647,648]],
            [0,"Dotations d'exploitation"],
            [1,"Sur immobilisations : dotations aux amortissements",[6811,6812]],
            [1,"Sur immobilisations : dotations aux dépréciations",[6816]],
            [1,"Pour risques et charges : dotations aux provisions ",[6815]],
            [0,"Autres charges",[651,653,654,658]]
        );
    }

    public function operationEnCommmun()
    {
        return array(
            [0,"Bénéfice attribué ou perte transférée (III)",[755]],
            [0,"Perte supportée ou bénefice transféré (IV)",[655]]
        );
    }

    public function productionsFinanciers()
    {
        return array(
            [0,"De participations",[755]],
            [0,"D'autres valeurs mobilières et créances de l'actif immobilisé",[655]],
            [0,"Autres interêts et produits assimilés",[763,764,765,768]],
            [0,"Reprises sur dépréciations et transferts de charges",[786,796]],
            [0,"Différences positives de change",[766]],
            [0,"Produits nets sur cessions de valeurs mobilières de placement",[767]]
        );
    }

    public function chargesFinanciers()
    {
        return array(
            [0,"Dotations financières aux ammortissments et dépréciations",[6861,6865,6866]],
            [0,"Interêts et charges assimilées",[661,664,665,668]],
            [0,"Différences négatives de change",[666]],
            [0,"Charges nettes sur cessions de valeurs mobilières de placement",[667]],
        );
    }

    public function produitExceptionnel()
    {
        return array(
            [0,"Produits exceptionnels sur opérations de gestion",[771]],
            [0,"Produits exceptionnels sur opérations en capital",[775,778,777]],
            [0,"Reprises sur dépreciations et transferts de charges",[787,797]]
        );
    }

    public function chargeExceptionnel()
    {
        return array(
            [0,"Charges exceptionnels sur opérations de gestion",[671]],
            [0,"Charges exceptionnels sur opérations en capital",[675,678]],
            [0,"Dotations exceptionnelles aux amortissements et aux provisions",[6871,6872,6873,6874,6875,6876]]
        );
    }

    public function impots()
    {
        return array(
            [0,"Impôt exigibles sur les résultats (IX)",[695,698]],
            [0,"Impôts différés (X)",[692,693]]
        );
    }

    
}