<?php
declare(strict_types=1);

namespace skewer\components\traits;


use skewer\base\section\Tree;
use skewer\base\site\Page;
use skewer\base\site\Site;

trait CanonicalOnPageTrait
{
    public function setCanonical(string $url)
    {
        $rootModule = Page::getRootModule();
        $rootModule->setData('canonical_url', $url);
    }

    public function setCanonicalByAlias(int $sectionId, string $alias)
    {
        $sectionPath = Tree::getSectionAliasPath(
            $sectionId,
            true,
            false,
            true
        );
        $canonical = Site::httpDomain() . $sectionPath . $alias;
        $this->setCanonical($canonical);
    }
}
