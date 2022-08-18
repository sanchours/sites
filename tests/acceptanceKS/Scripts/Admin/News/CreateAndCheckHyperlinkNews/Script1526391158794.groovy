/**
 * @description Создание и проверка новости со ссылкой
 * @step Переходим на вкладку новости
 * @step Нажатие на кнопку "Добавить новость"
 * @step Заполнение поля Ссылка
 * @step Нажатие на кнопку "Сохранить"
 * @step Проверка установлена ли галочка активность, если её нет то устанавливает её
 * @step Переход на вкладку редактор
 * @step Нажатие на ссылку перехода на пользовательскую часть
 * @step Переход на страницу публичной части 
 * @step Переходим в первую новость
 * @step Проверяем URL открытой страницы, в случае несоответствия выдается ошибка
 */import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
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
import com.kms.katalon.core.exception.StepErrorException
import internal.GlobalVariable as GlobalVariable
import data.NewsData as NewsData

NewsData DataNews = new NewsData()

WebUI.click(findTestObject('CMS/News/CreateNewNews/span_news'))

WebUI.click(findTestObject('CMS/News/CreateNewNews/span_new_news'))

WebUI.setText(findTestObject('CMS/News/CreateAndCheckHyperlinkNews/input_hyperlink_news'), DataNews.link)

WebUI.click(findTestObject('CMS/News/CreateNewNews/span_save'))

UrlInput = DataNews.checklink

if (WebUI.verifyElementChecked(findTestObject('CMS/News/CheckNews/div_news_is_active'), 1, FailureHandling.OPTIONAL) ==
	true) {
	WebUI.click(findTestObject('CMS/News/CheckNews/div_news_is_active'))
	}
	
		WebUI.click(findTestObject('Site/News/NewsCheck/span_news_ediror_tab'))
	
		WebUI.click(findTestObject('Site/News/NewsCheck/a_news_page_link'))
	
		WebUI.switchToWindowIndex(1)
	
		WebUI.click(findTestObject('Site/News/NewsCheck/a_title_news'))
		
		Url = WebUI.getUrl()
		
		if (Url != UrlInput) {
		 
		 throw new com.kms.katalon.core.exception.StepErrorException('Адрес результирующей страницы неверен!')
	}