<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* Index/index.html */
class __TwigTemplate_4054db4fa0b41d70eb107f6b4ecbc55d80c232d70238324b1cbd529ea6922634 extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        echo "<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <title>Welcome to Test Site!</title>
    <style>
        body {
            width: 35em;
            margin: 0 auto;
            font-family: Tahoma, Verdana, Arial, sans-serif;
        }
    </style>
</head>
<body>
<h1>Hello, ";
        // line 15
        echo twig_escape_filter($this->env, ($context["name"] ?? null), "html", null, true);
        echo "! Welcome to Test Site!</h1>
</body>
</html>";
    }

    public function getTemplateName()
    {
        return "Index/index.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  53 => 15,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "Index/index.html", "/var/www/html/telegramsdk/scr/View/Index/index.html");
    }
}
