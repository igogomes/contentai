## CONTENTS OF THIS FILE

* Introduction
* Requirements
* Recommended Modules
* Installation
* Configuration

## INTRODUCTION

Content AI is a powerful module that leverages the OpenAI platform to automatically generate high-quality content. It can be used to produce SEO content such as titles, keywords and meta descriptions for articles, provide translated content for articles and pages, as well as for other purposes.

* For a full description of the module visit: <https://www.drupal.org/project/contentai>

* To submit bug reports and feature suggestions, or to track changes visit: <https://www.drupal.org/project/issues/contentai>

## REQUIREMENTS

This module requires the following modules:

* Metatag - https://www.drupal.org/project/metatag
* OpenAI / ChatGPT / AI Search Integration - https://www.drupal.org/project/openai
* Workflow - https://www.drupal.org/project/workflow

## RECOMMENDED MODULES

* No extra module is required.

## INSTALLATION

* Install the Content AI module as you would normally install a contributed Drupal module. Visit <https://www.drupal.org/docs/extending-drupal/installing-modules> for further information.

## CONFIGURATION

### SEO content generation

The module provides several settings, which you can access via yoursite/admin/config/contentai/settings:

* Option: this determines the type of information that will be generated automatically with the support of GPT. The initial options available include Title, Keywords, and Description;

* Model: this refers to the version of GPT that will be used for generating the content. Currently, text-babbage-001, text-curie-001, and text-davinci-003 are available, but more versions may be added in the future;

* Temperature: this setting controls the tone of the content that is automatically generated. You can adjust it to make the tone more serious or friendly, depending on your needs;

* Meta tag field selector: this setting determines the ID of the field that will receive the automatically generated content for an option (Title, Keyword, or Description). For example, the field that will receive the automatically generated content for the title is #edit-field-description-0-basic-title on the article creation page;

* Question: this is the question that will be asked to GPT to generate the title, keywords, description, or other information automatically. In the question, the shortcode {body} is used to represent the text of the article, so the question will also be asked considering this text.

Once you have configured the settings, you can simply go to the article edition page (yoursite/node/node-number/edit) and select one or more options in the Meta Content AI section (Description, Title, or Keywords). Click on the Generate button, and the content will be automatically generated based on the settings you have specified, as well as the article text.

The generated content will be inserted into the page title, description, and keywords fields under Basic tags in the Description section. You can find the Meta Content AI and Description sections in the right column of the article creation/edit page.

It is important to note that the Meta Content AI section will only be displayed after you have saved the initial text of the article (in the Body field), as this content will be used for automatic generation through GPT. With Content AI, you can save time and effort while producing high-quality content that meets your needs.

### Translation

The translation feature is a powerful tool that enables you to translate articles and pages into different languages. You can access these types of content through the /admin/content page.

To translate an already published article or page, click on the Edit button, and then select the Translate AI option located above the title. You can then choose one or more languages from the available options and click Translate. This will generate a new translated version of the article or page in the selected language(s), which will have the status of unpublished.

It is important to note that if you update an article or page, you must also update any previously made translations. To do so, you must access the Translate AI tab within the updated article or page, select the language(s) you want to translate, and click the Translate button. If a language has already been translated, it will be displayed with the option to "overwrite translation".

After a translation has been made or updated, its status changes to Unpublished. It is crucial to check that the article or page is properly available and published.

If you select more than one language to translate, the processing time may increase. Therefore, it is important to consider the number of languages selected before initiating the translation process.