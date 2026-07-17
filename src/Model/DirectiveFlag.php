<?php

declare(strict_types=1);

namespace IntegerNet\SansecWatch\Model;

enum DirectiveFlag: string
{
    case NoneAllowed          = 'none_allowed';
    case SelfAllowed          = 'self_allowed';
    case InlineAllowed        = 'inline_allowed';
    case EvalAllowed          = 'eval_allowed';
    case DynamicAllowed       = 'dynamic_allowed';
    case EventHandlersAllowed = 'event_handlers_allowed';
}
