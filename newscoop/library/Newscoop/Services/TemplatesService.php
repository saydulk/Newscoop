<?php
/**
 * @package Newscoop
 * @copyright 2014 Sourcefabric o.p.s.
 * @author Paweł Mikołąjczuk <pawel.mikolajczuk@sourcefabric.org>
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\Services;

use Newscoop\Services\PublicationService;

/**
 *  Templates service
 */
class TemplatesService
{
    /**
     * @var \CampTemplate
     */
    protected $smarty;

    /**
     * Themes Service
     *
     * @var ThemesServiceInterface
     */
    protected $themesService;

    /**
     * Publication Service
     *
     * @var PublicationService
     */
    protected $publicationService;

    protected $originalVector;

    public function __construct(ThemesService $themesService, PublicationService $publicationService)
    {
        $this->smarty = \CampTemplate::singleton();
        $this->smarty->assign('gimme', $this->smarty->context());
        $this->themesService = $themesService;
        $this->publicationService = $publicationService;
        $this->originalVector = $this->smarty->campsiteVector;
        $this->preconfigureSmarty();
    }

    /**
     * Fetch template with smarty
     *
     * @param string  $file     template file path
     * @param array   $params   array with template parameters
     * @param integer $lifetime template cache lifetime (default: 1400 seconds)
     * 
     * @return string Template output
     */
    public function fetchTemplate($file, $params = array(), $lifetime = 1400)
    {
        $content = $this->renderTemplate($file, $params, $lifetime, false);
        $this->preconfigureVector();

        return $content;
    }

    /**
     * Render template with smarty (will echo outupt)
     *
     * @param string  $file     template file path
     * @param array   $params   array with template parameters
     * @param integer $lifetime template cache lifetime (default: 1400 seconds)
     * @param boolean $render   render or just fetch template (default: true [render])
     * 
     * @return string Template output
     */
    public function renderTemplate($file, $params = array(), $lifetime = 1400, $render = true)
    {
        $this->assignParameters($params);
        $this->setLifetime($lifetime);

        return $this->smarty->fetch($file, sha1(serialize($this->smarty->campsiteVector)), null, null, $render);
    }

    /**
     * Get current smarty object
     *
     * @return \Smarty
     */
    public function getSmarty()
    {
        return $this->smarty;
    }

    /**
     *  Newscoop caching save cached template file content with special vector parameters.
     *  
     *  By default vector is filled with 6 parameters:
     *  * (int) language
     *  * (int) publication
     *  * (int) issue
     *  * (int) section
     *  * (int) article 
     *  * (string) params
     *
     *  In this service vector is allways prefilled only with publication and language values.
     *  You need to set manualny (if needed) issue, section, article or params keys
     *  
     * @param array $vector
     */
    public function setVector($vector)
    {
        $this->smarty->campsiteVector = $vector;
    }

    /**
     * Set template cache lifetime
     *
     * @param integer $lifetime
     */
    public function setLifetime($lifetime)
    {
        $this->smarty->cache_lifetime = $lifetime;
    }

    private function assignParameters($params = array())
    {
        foreach ($params as $key => $value) {
            $this->smarty->assign($key, $value);
        }
    }

    private function preconfigureSmarty()
    {
        $this->smarty->addTemplateDir(realpath(APPLICATION_PATH . '/../themes/' . $this->themesService->getThemePath()));
        $this->smarty->config_dir = (realpath(APPLICATION_PATH . '/../themes/' . $this->themesService->getThemePath() . '_conf'));

        // reverse templates dir order
        $this->smarty->setTemplateDir(array_reverse($this->smarty->getTemplateDir()));

        $this->preconfigureVector();
    }

    private function preconfigureVector()
    {
        $publicationMetadata = $this->publicationService->getPublicationMetadata();
        if (count($publicationMetadata) > 0) {
            $this->smarty->campsiteVector = array_merge($this->originalVector, array(
                'publication' => $publicationMetadata['alias']['publication_id'],
                'language' => $publicationMetadata['publication']['id_default_language']
            ));
        }
    }
}
