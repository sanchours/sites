 /**
 * @description Проверка статьи с источником
 * @step Переход во вкладку "Статьи"
 * @step Создаем статью с заполненым полем источник и полный текст статьи
 * @step Проверяем наличие галочки у активность, если её нет то ставит её
 * @step Переход на вкладку редактор
 * @step Нажатие на ссылку перехода на пользовательскую часть
 * @step Переход на страницу публичной части 
 * @step Переходим в первую статью
 * @step Жмем на ссылку источник
 * @step Проверяем URL открытой страницы, в случае несоответствия выдается ошибка
 */ import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import com.kms.katalon.core.checkpoint.Checkpoint as Checkpoint
import com.kms.katalon.core.checkpoint.CheckpointFactory as CheckpointFactory
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords as MobileBuiltInKeywords
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords as Mobile
import com.kms.katalon.core.model.FailureHandling as FailureHandling
import com.kms.katalon.core.testcase.TestCase as TestCase
import com.kms.katalon.core.testcase.TestCaseFactory as TestCaseFactory
import com.kms.katalon.core.testdata.TestData as TestData
import com.kms.katalon.core.testdata.TestDataFactory as TestDataFactory
import com.kms.katalon.core.testobject.ObjectRepository as ObjectRepository
import com.kms.katalon.core.testobject.TestObject as TestObject
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WSBuiltInKeywords
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WS
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUiBuiltInKeywords
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUI
import com.kms.katalon.core.exception.StepErrorException as StepErrorException
import internal.GlobalVariable as GlobalVariable
import data.ArticlesData as ArticlesData

ArticlesData DataArticles = new ArticlesData()

WebUI.click(findTestObject('CMS/Articles/CreateArticles/span_articles'))

WebUI.click(findTestObject('CMS/Articles/CreateArticles/span_new_articles'))

WebUI.setText(findTestObject('CMS/Articles/CreateArticles/body_full_text_articles'), DataArticles.ftext)

WebUI.setText(findTestObject('CMS/Articles/CheckSourcelinkArticles/input_source_link_articles'), DataArticles.link)

WebUI.click(findTestObject('CMS/Articles/CreateArticles/span_save_articles'))

UrlInput = DataArticles.checklink

if (WebUI.verifyElementChecked(findTestObject('Object Repository/CMS/Articles/CheckArticlesIsActive/div_articles_is_active'), 
    1, FailureHandling.OPTIONAL) == true) {
    WebUI.click(findTestObject('Object Repository/CMS/Articles/CheckArticlesIsActive/div_articles_is_active'))
}

WebUI.click(findTestObject('CMS/Articles/CheckArticlesIsActive/span_articles_editor_tab'))

WebUI.click(findTestObject('CMS/Articles/CheckArticlesIsActive/a_articles_page_link'))

WebUI.switchToWindowIndex(1)

WebUI.click(findTestObject('Site/Articles/CheckArticles/a_articles_title'))

WebUI.click(findTestObject('Site/Articles/CheckSourcelinkArticles/a_source_link_articles'))

WebUI.switchToWindowIndex(2)

Url = WebUI.getUrl()

if (Url != UrlInput) {
    throw new StepErrorException('Адрес результирующей страницы неверен!')
}

