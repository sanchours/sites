 /**
 * @description Сторонняя результирующая страница
 * @step Переход на детальную формы
 * @step Переход в настройки результирующей
 * @step Выбор сторонней результирующей
 * @step Установка URL для сторонней результирующей
 * @step Переход в верхнее меню
 * @step Переход в страницу с формой
 * @step Отправка формы
 * @step Проверка соответствия URL
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
import com.kms.katalon.core.exception.StepErrorException as StepErrorException
import urlSite.BaseLink as BaseLink
import com.kms.katalon.core.cucumber.keyword.CucumberBuiltinKeywords as CucumberKW

UrlInput = (BaseLink.getUrlDef() + '/')

WebUI.doubleClick(findTestObject('CMS/Form/ShowForrmHeader/div_form_feedback'))

WebUI.click(findTestObject('CMS/Form/ResultPage/span_resultant_page'))

WebUI.click(findTestObject('CMS/Form/ResultPage/input_type_result_page'))

WebUI.click(findTestObject('CMS/Form/ResultPage/li_sided'))

WebUI.setText(findTestObject('CMS/Form/ResultPage/input_form_redirect'), UrlInput)

WebUI.click(findTestObject('CMS/Form/ResultPage/span_save'))

WebUI.click(findTestObject('CMS/Form/CheckboxLicenseAgreement/div_section_list'))

WebUI.click(findTestObject('CMS/Reviews/SectionReviewIdOnTheMain/div_top_menu'))

WebUI.click(findTestObject('CMS/Form/ShowForrmHeader/div_contacts'))

WebUI.click(findTestObject('CMS/Form/ShowForrmHeader/div_contacts'))

WebUI.click(findTestObject('Site/ReviewCheck/a_review_page_link'))

WebUI.switchToWindowIndex(1)

WebUI.setText(findTestObject('Site/CreateReview/input_email'), 'test@test.te')

WebUI.setText(findTestObject('CMS/Form/HiddenField/textarea_text'), 'Текст')

if (WebUI.verifyElementVisible(findTestObject('Site/CreateReview/label_form_checkbox_private_policy'), FailureHandling.OPTIONAL)) {
    WebUI.click(findTestObject('Site/CreateReview/label_form_checkbox_private_policy'))
}

WebUI.click(findTestObject('Site/TestCallBack/button_send'))

Url = WebUI.getUrl()

if (Url != UrlInput) {
    throw new StepErrorException('Адрес результирующей страницы неверен!', FailureHandling.OPTIONAL)
}

