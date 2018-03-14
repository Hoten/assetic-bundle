<?php

namespace CourseHero\UtilsBundle\Assetic;

use Assetic\Asset\AssetInterface;
use Assetic\Filter\FilterInterface;
use Assetic\Filter\UglifyJs2Filter;

class BundledAppFilter implements FilterInterface
{
    /**
     * @inheritdoc
     */
    public function filterLoad(AssetInterface $asset)
    {
    }

    /**
     * @inheritdoc
     *
     * Goal: take as input one single bundled JS application and copy any source map into sym-assets.
     *
     * ex results: input '../../js/dist/proco/annotations/app.js' =>
     * sym-assets/js/0da6667_proco-annotations-app.js
     * sym-assets/js/0da6667_proco-annotations-app.js.map
     *
     * Because the UglifyJS filter would strip source maps, disable it (see CHUglifyJs2Filter)
     * Bundled code should already be minified.
     * Attempt to rename asset based on bundle name.
     * Add source map attribute for browser debugging: //# sourceMappingURL=<asset basename>.map
     * 
     */
    public function filterDump(AssetInterface $asset)
    {
        $content = $asset->getContent();
        // $content = '';
        // $content = $content . "console.log('===========');\n";
        // $content = $content . "// this is a test for science\n";


        // $content = $content . "console.log('===========');";
        // $content = $content . "console.log('$appName');";
        // $content = $content . "console.log('$newTargetPath');";
        // $content = $content . "console.log('${end(str_split($asset->getTargetPath(), '/'))}');";
        // $asset->setContent($content);
        // return;


        // $asset->getTargetPath() can look like 'js/0da6667_app_1.js'
        // rename the basename (last part) to '0da6667_$appName.js'
        // $targetPathParts = explode('/', $asset->getTargetPath());
        // $partToReplace = explode(
        //     '_', end($targetPathParts), 2
        // )[1];
        // $newTargetPath = str_replace($partToReplace, $appName . '.js', $asset->getTargetPath());
        // $newTargetBasename = basename($newTargetPath);
        // $asset->setTargetPath($newTargetPath);

        // var_dump("in the filter yo \n");
        // var_dump($asset->getSourcePath());
        // $root = rtrim($this->asseticDir, '/');
        $root = $asset->getSourceRoot();
        $symAssetsRoot = "/var/www/html/coursehero/src/Control/sym-assets/";
        
        // ensure folder exists
        // if (!file_exists($symAssetsRoot . 'js')) {
        //     mkdir($symAssetsRoot . 'js', 0755, true);
        // }

        // $targetPathOnDisk = $symAssetsRoot . str_replace('_controller/', '', $asset->getTargetPath());
        $from = $asset->getSourceRoot() . '/' . $asset->getSourcePath() . '.map';

        // $asset->getTargetPath() can look like '_controller/js/0da6667_app_1.js'
        $targetPath = str_replace('_controller/', '', $asset->getTargetPath());
        $to = $symAssetsRoot . $targetPath . '.map';
        
        $errors = false;
        if (file_exists($from) && !copy($from, $to)) {
            $errors = error_get_last();
            throw new \Exception('issue copying source map ' . $errors['type'] . ' ' . $errors['message']);
        }

        $log = '';
        $log = $log . "console.log('$from');";
        $log = $log . "console.log('$to');";
        if ($errors) {
            $type = $errors['type'];
            $message = $errors['message'];
            $log = $log . "console.log('$type');";
            $log = $log . "console.log('$message');";
        }
        // $asset->setContent($content . "\n$log\n" . "\n//# sourceMappingURL=" . basename($to));
        $asset->setContent($content . "\n//# sourceMappingURL=" . 'https://coursehero.local/sym-assets/' . $targetPath . '.map');
        // $asset->setContent($content);
    }
}
