<?php

namespace Philly\DI;

enum BindingLifetime
{
	case Transient;
	case Request;
}
