 /**
 * @description Создание ВИО в пользовательской части
 * @step Сохраняем урл открытой страницы
 * @step Переход на страницу публичной части 
 * @step Заполнение полей имя, почта, город и текст вопроса
 * @step Согласие с политикой конфедациальности
 * @step Нажатие на кнопку отправить
 * @step Проверка наличия блока результирующей
 * @step Переход на сохраненый урл
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
import com.kms.katalon.core.webui.keyword.builtin.GetUrlKeyword
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUI
import internal.GlobalVariable as GlobalVariable
import data.FAQData as FAQData
import urlSite.BaseLink as BaseLink

FAQData DataFAQ = new FAQData()

WebUI.click(findTestObject('CMS/FAQ/CheckFAQ/a_faq_page_link'))

WebUI.switchToWindowIndex(1)

//Url = WebUI.getUrl()
//
//WebUI.navigateToUrl(BaseLink.getUrlDef() + '/' + DataFAQ.aliassection)

WebUI.setText(findTestObject('Site/FAQ/SendFAQ/input_city_faq_site'), DataFAQ.city)

WebUI.setText(findTestObject('Site/FAQ/SendFAQ/input_email_faq_site'), DataFAQ.email)

WebUI.setText(findTestObject('Site/FAQ/SendFAQ/input_name_faq_site'), DataFAQ.name)

WebUI.setText(findTestObject('Site/FAQ/SendFAQ/textarea_content_faq_site'), DataFAQ.question)

WebUI.click(findTestObject('Site/FAQ/SendFAQ/label_private_policy_faq'))

WebUI.click(findTestObject('Site/FAQ/SendFAQ/button_seng_faq'))

WebUI.verifyElementVisible(findTestObject('Site/FAQ/SendFAQ/div_response_faq'))

WebUI.switchToWindowIndex(0)

//WebUI.navigateToUrl(Url)


