<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model;

use function str_replace;

enum Directive: string
{
    case DefaultSrc     = 'default-src';
    case ChildSrc       = 'child-src';
    case ConnectSrc     = 'connect-src';
    case FontSrc        = 'font-src';
    case FrameSrc       = 'frame-src';
    case ImgSrc         = 'img-src';
    case ManifestSrc    = 'manifest-src';
    case MediaSrc       = 'media-src';
    case ObjectSrc      = 'object-src';
    case ScriptSrc      = 'script-src';
    case StyleSrc       = 'style-src';
    case BaseUri        = 'base-uri';
    case FormAction     = 'form-action';
    case FrameAncestors = 'frame-ancestors';

    public function configKey(): string
    {
        return str_replace('-', '_', $this->value);
    }
}
