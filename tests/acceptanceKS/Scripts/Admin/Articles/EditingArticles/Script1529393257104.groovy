 /**
 * @description Проверка редактирования статьи
 * @step Проверка соответствия названия первой статьи списка с названием добавленной статьи
 * @step Проверка установлена ли галочка активность, если её нет то устанавливает её
 * @step Переход на вкладку редактор
 * @step Нажатие на ссылку перехода на пользовательскую часть
 * @step Переход на страницу публичной части 
 * @step Проверяем соответствие названия и аннонсного текста у первой статьи
 * @step Переходим по первой статье
 * @step Проверяем соответствие полного текста статьи
 * @step Возвращение окна с панелью администрирования
 * @step Переход на вкладку статей 
 * @step Редактирование первой статьи в списке и сохранение её 
 * @step Проверка установлена ли галочка активность, если её нет то устанавливает её 
 * @step Переход на вкладку редактор
 * @step Нажатие на ссылку перехода на пользовательскую часть
 * @step Переход на страницу публичной части 
 * @step Проверяем соответствие измененного названия и аннонсного текста у первой статьи
 * @step Переходим по первой статье
 * @step Проверяем соответствие измененного полного текста статьи 
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
import internal.GlobalVariable as GlobalVariable
import data.ArticlesData as ArticlesData

ArticlesData DataArticles = new ArticlesData()

WebUI.verifyElementText(findTestObject('CMS/Articles/CheckArticles/div_created_articles_title'), DataArticles.title)

if (WebUI.verifyElementChecked(findTestObject('Object Repository/CMS/Articles/CheckArticlesIsActive/div_articles_is_active'), 1, FailureHandling.OPTIONAL) == 
true) {
    WebUI.click(findTestObject('Object Repository/CMS/Articles/CheckArticlesIsActive/div_articles_is_active'))
}

WebUI.click(findTestObject('CMS/Articles/CheckArticlesIsActive/span_articles_editor_tab'))

WebUI.click(findTestObject('CMS/Articles/CheckArticlesIsActive/a_articles_page_link'))

WebUI.switchToWindowIndex(1)

WebUI.verifyElementText(findTestObject('Site/Articles/CheckArticles/a_articles_title'), DataArticles.title)

WebUI.verifyElementText(findTestObject('Site/Articles/CheckArticles/div_text_of_the_announcement_articles'), DataArticles.аtext)

WebUI.click(findTestObject('Site/Articles/CheckArticles/a_articles_title'))

WebUI.verifyElementText(findTestObject('Site/Articles/CheckArticles/div_text_articles'), DataArticles.ftext)

DataArticles.setChangeData()

WebUI.switchToWindowIndex(0)

WebUI.click(findTestObject('CMS/Articles/CreateArticles/span_articles'))

WebUI.doubleClick(findTestObject('CMS/Articles/EditingArticles/div_first_articles_in_the_list'))

WebUI.setText(findTestObject('CMS/Articles/CreateArticles/input_title_articles'), DataArticles.title)

WebUI.setText(findTestObject('CMS/Articles/CreateArticles/body_announcement_articles'), DataArticles.аtext)

WebUI.setText(findTestObject('CMS/Articles/CreateArticles/body_full_text_articles'), DataArticles.ftext)

WebUI.click(findTestObject('CMS/Articles/CreateArticles/span_save_articles'))

WebUI.verifyElementText(findTestObject('CMS/Articles/CheckArticles/div_created_articles_title'), DataArticles.title)

if (WebUI.verifyElementChecked(findTestObject('Object Repository/CMS/Articles/CheckArticlesIsActive/div_articles_is_active'), 1, FailureHandling.OPTIONAL) == 
true) {
    WebUI.click(findTestObject('Object Repository/CMS/Articles/CheckArticlesIsActive/div_articles_is_active'))
}

WebUI.click(findTestObject('CMS/Articles/CheckArticlesIsActive/span_articles_editor_tab'))

WebUI.click(findTestObject('CMS/Articles/CheckArticlesIsActive/a_articles_page_link'))

WebUI.switchToWindowIndex(2)

WebUI.verifyElementText(findTestObject('Site/Articles/CheckArticles/a_articles_title'), DataArticles.title)

WebUI.verifyElementText(findTestObject('Site/Articles/CheckArticles/div_text_of_the_announcement_articles'), DataArticles.аtext)

WebUI.click(findTestObject('Site/Articles/CheckArticles/a_articles_title'))

WebUI.verifyElementText(findTestObject('Site/Articles/CheckArticles/div_text_articles'), DataArticles.ftext)
