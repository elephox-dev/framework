<?php

namespace Philly\DI;

enum InjectionLifetime
{
	case Transient;
	case Request;
}
