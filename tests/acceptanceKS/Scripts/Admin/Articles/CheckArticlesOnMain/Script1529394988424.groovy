 /**
 * @description Проверка активности новой статьи на главной
 * @step Проверка что статья есть в списке новостей в панели администрирования
 * @step Проверка установлена ли галочка активность, если её нет то устанавливает её
 * @step Проверка установлена ли галочка на главной, если её нет то устанавливает её 
 * @step Переход на вкладку редактор
 * @step Нажатие на ссылку перехода на пользовательскую часть
 * @step Переход на страницу публичной части 
 * @step Переходим на главную страницу
 * @step Проверяем соответствие названия и аннонсного текста у первой статьи
 * @step Возвращение окна с панелью администрирования
 * @step Переход на вкладку статей
 * @step Отключение галочки на главной
 * @step Возвращение окна с пользовательской частью
 * @step Проверка на отсутствие на странице названия и анонсного текста статьи
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
import urlSite.BaseLink as BaseLink
import internal.GlobalVariable as GlobalVariable
import data.ArticlesData as ArticlesData

ArticlesData DataArticles = new ArticlesData()

WebUI.verifyElementText(findTestObject('Object Repository/CMS/Articles/CheckArticlesOnMain/div_created_articles_title_aom'), DataArticles.title)

if (WebUI.verifyElementChecked(findTestObject('Object Repository/CMS/Articles/CheckArticlesOnMain/div_articles_is_active_aom'), 0, FailureHandling.OPTIONAL) == 
true) {
    WebUI.click(findTestObject('Object Repository/CMS/Articles/CheckArticlesOnMain/div_articles_is_active_aom'))
}

if (WebUI.verifyElementChecked(findTestObject('Object Repository/CMS/Articles/CheckArticlesOnMain/div_articles_on_main_aom'), 1, FailureHandling.OPTIONAL) == 
false) {
    WebUI.click(findTestObject('Object Repository/CMS/Articles/CheckArticlesOnMain/div_articles_on_main_aom'))
}

WebUI.click(findTestObject('CMS/Articles/CheckArticlesIsActive/span_articles_editor_tab'))

WebUI.click(findTestObject('CMS/Articles/CheckArticlesIsActive/a_articles_page_link'))

WebUI.switchToWindowIndex(1)

WebUI.navigateToUrl(BaseLink.getUrlDef())

WebUI.verifyElementText(findTestObject('Site/Articles/CheckArticlesOnMain/a_article_on_main'), DataArticles.title)

WebUI.verifyElementText(findTestObject('Site/Articles/CheckArticlesOnMain/div_announcment_articles_on_main'), DataArticles.аtext)

WebUI.switchToWindowIndex(0)

WebUI.click(findTestObject('CMS/Articles/CreateArticles/span_articles'))

WebUI.click(findTestObject('Object Repository/CMS/Articles/CheckArticlesOnMain/div_articles_on_main_aom'))

WebUI.switchToWindowIndex(1)

WebUI.refresh()

WebUI.verifyTextNotPresent(DataArticles.title, false)

WebUI.verifyTextNotPresent(DataArticles.аtext, false)

