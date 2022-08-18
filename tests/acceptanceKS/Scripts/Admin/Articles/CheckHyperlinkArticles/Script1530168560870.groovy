 /**
 * @description Создание и проверка статьи со ссылкой
 * @step Переходим на вкладку статьи
 * @step Создаем статьи с заполненым полем ссылка
 * @step Проверка установлена ли галочка активность, если её нет то устанавливает её
 * @step Переход на вкладку редактор
 * @step Нажатие на ссылку перехода на пользовательскую часть
 * @step Переход на страницу публичной части 
 * @step Переходим в первую статью
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

WebUI.setText(findTestObject('CMS/Articles/CheckHyperlink/input_hyperlink_articles'), DataArticles.link)

WebUI.click(findTestObject('CMS/Articles/CreateArticles/span_save_articles'))

UrlInput = DataArticles.checklink

if (WebUI.verifyElementChecked(findTestObject('CMS/News/CheckNews/div_news_is_active'), 1, FailureHandling.OPTIONAL) == 
true) {
    WebUI.click(findTestObject('CMS/Articles/CheckArticlesIsActive/div_articles_is_active'))
}

WebUI.click(findTestObject('CMS/Articles/CheckArticlesIsActive/span_articles_editor_tab'))

WebUI.click(findTestObject('CMS/Articles/CheckArticlesIsActive/a_articles_page_link'))

WebUI.switchToWindowIndex(1)

WebUI.click(findTestObject('Site/Articles/CheckArticles/a_articles_title'))

Url = WebUI.getUrl()

if (Url != UrlInput) {
    throw new StepErrorException('Адрес результирующей страницы неверен!')
}

