<?php

namespace tests\codeception\fixtures\base;

use yii\test\Fixture;

/** Набор базовых фикстур. */
class BaseFixture extends Fixture
{
    public $depends = [
        '\tests\codeception\fixtures\base\CeChasyFixture',
        '\tests\codeception\fixtures\base\CeDopolnitelnyeParametryFixture',
        '\tests\codeception\fixtures\base\CdMaterialKorpusaFixture',
        '\tests\codeception\fixtures\base\CdMaterialRemeshkaFixture',
        '\tests\codeception\fixtures\base\CdSpecialnyeFunkciiFixture',
        '\tests\codeception\fixtures\base\CdCvetaFixture',
        '\tests\codeception\fixtures\base\CdNaznachenieFixture',
        '\tests\codeception\fixtures\base\CdBrendyFixture',
        '\tests\codeception\fixtures\base\CdStilFixture',
        '\tests\codeception\fixtures\base\CGoodsFixture',
        '\tests\codeception\fixtures\base\ClSectionFixture',
        '\tests\codeception\fixtures\base\ClSemanticFixture',
        '\tests\codeception\fixtures\base\CrChasyNaznachenieFixture',
        '\tests\codeception\fixtures\base\CrChasySpecialnyeFunkciiFixture',
        '\tests\codeception\fixtures\base\CoBaseCardFixture',
        '\tests\codeception\fixtures\base\CEntityFixture',
        '\tests\codeception\fixtures\base\GroupPolicyDataFixture',
        '\tests\codeception\fixtures\base\GroupPolicyFixture',
        '\tests\codeception\fixtures\base\GroupPolicyFuncFixture',
        '\tests\codeception\fixtures\base\GroupPolicyModuleFixture',
        '\tests\codeception\fixtures\base\SysVarsFixture',
        '\tests\codeception\fixtures\base\TreeSectionFixture',
        '\tests\codeception\fixtures\base\ParametersFixture',
        '\tests\codeception\fixtures\base\ArticlesFixture',
        '\tests\codeception\fixtures\base\NewsFixture',
        '\tests\codeception\fixtures\base\FaqFixture',
        '\tests\codeception\fixtures\base\PhotoGalleryAlbumsFixture',
        '\tests\codeception\fixtures\base\PhotoGalleryFormatsFixture',
        '\tests\codeception\fixtures\base\PhotoGalleryPhotosFixture',
        '\tests\codeception\fixtures\base\PhotoGalleryProfilesFixture',
        '\tests\codeception\fixtures\base\SearchIndexFixture',
        '\tests\codeception\fixtures\base\RegistryStorageFixture',
        '\tests\codeception\fixtures\base\SeoTemplateFixture',
        '\tests\codeception\fixtures\base\SeoDataFixture',
    ];
}
