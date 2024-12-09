<?php

namespace common\assets\scssConverter;

use common\assets\scssConverter\storage\{FsStorage, Storage};
use RuntimeException;
use ScssPhp\ScssPhp\{Compiler, Exception\SassException};
use Yii;
use yii\base\{Component, InvalidConfigException};
use yii\web\AssetConverterInterface;

/**
 * @package common\assets\scssConverter
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class ScssAssetConverter extends Component implements AssetConverterInterface
{
    public Storage $storage;

    /**
     * Whether the source asset file should be converted even if
     * its result already exists. You may want to set this to be `true` during
     * the development stage to make sure the converted assets are always up-to-
     * date. Do not set this to true on production servers as it will
     * significantly degrade the performance.
     */
    public bool $forceConvert = false;

    /**
     * ScssPhp Compiler object which does the actual work
     */
    public Compiler $compiler;

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        if (!isset($this->storage)) {
            $this->storage = new FsStorage();
        }
        if (!isset($this->compiler)) {
            /** @var Compiler $compiler */
            $compiler = Yii::createObject(Compiler::class);
            $this->compiler = $compiler;
        }
    }

    /**
     * Converts a given SCSS asset file into a CSS file.
     *
     * @param string $asset    the asset file path, relative to $basePath
     * @param string $basePath the directory the $asset is relative to.
     *
     * @return string the converted asset file path, relative to $basePath.
     * @throws SassException
     */
    public function convert($asset, $basePath): string
    {
        $extension = $this->getExtension($asset);
        if ($extension !== 'scss') {
            return $asset;
        }
        $cssAsset = $this->getCssAsset($asset, 'css');

        $inFile = "$basePath/$asset";
        $outFile = "$basePath/$cssAsset";

        $this->compiler->setImportPaths(dirname($inFile));

        if (!$this->storage->exists($inFile)) {
            Yii::error("Input file $inFile not found.", __METHOD__);
            return $asset;
        }

        $this->convertAndSaveIfNeeded($inFile, $outFile);

        return $cssAsset;
    }

    private function getExtension(string $filename): string
    {
        return pathinfo($filename, PATHINFO_EXTENSION);
    }

    /**
     * Get the relative path and filename of the asset
     * @param string $filename e.g. path/asset.css
     * @param string $newExtension e.g. scss
     * @return string e.g. path/asset.scss
     */
    protected function getCssAsset(string $filename, string $newExtension): string
    {
        $extensionlessFilename = pathinfo($filename, PATHINFO_FILENAME);
        /** @var int $filenamePosition */
        $filenamePosition = strrpos($filename, $extensionlessFilename);
        $relativePath = substr($filename, 0, $filenamePosition);
        return "$relativePath$extensionlessFilename.$newExtension";
    }

    /**
     * @throws SassException
     */
    private function convertAndSaveIfNeeded(string $inFile, string $outFile): void
    {
        if ($this->shouldConvert($inFile, $outFile)) {
            $css = $this->compiler->compileString($this->storage->get($inFile), $inFile);
            $this->storage->put($outFile, $css->getCss());
        }
    }

    private function shouldConvert(string $inFile, string $outFile): bool
    {
        if (!$this->storage->exists($outFile)) {
            return true;
        }
        if ($this->forceConvert) {
            return true;
        }
        try {
            return $this->isOlder($outFile, $inFile);
        } catch (RuntimeException $e) {
            Yii::warning('Encountered RuntimeException message "' . $e->getMessage() . '", going to convert.', __METHOD__);
            return true;
        }
    }

    private function isOlder(string $fileA, string $fileB): bool
    {
        return $this->storage->getMtime($fileA) < $this->storage->getMtime($fileB);
    }
}
