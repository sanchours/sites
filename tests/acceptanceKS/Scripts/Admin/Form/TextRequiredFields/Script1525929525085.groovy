 /**
 * @description Вывод заголовок формы
 * @step Переход на детальную формы обратной связи
 * @step Перход в настройки формы
 * @step Установка галочки "* - обязательные для заполнения поля"
 * @step Нажатие на кнопку "Сохранить"
 * @step Переход в верхнее меню
 * @step Переход в раздел "Контакты"
 * @step Переход по ссылке в раздел "Контакты" в пользовательской части
 * @step Проверка наличия блока "* - обязательные для заполнения поля"
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
import com.kms.katalon.core.cucumber.keyword.CucumberBuiltinKeywords as CucumberKW

WebUI.doubleClick(findTestObject('CMS/Form/ShowForrmHeader/div_form_feedback'))

WebUI.click(findTestObject('CMS/Form/ShowForrmHeader/span_form_settings'))

if (WebUI.verifyElementChecked(findTestObject('CMS/Form/TextRequiredFields/input_required_fields'), 0, FailureHandling.OPTIONAL) ==
	true) {
	WebUI.click(findTestObject('CMS/Form/TextRequiredFields/input_required_fields'))
	}

WebUI.click(findTestObject('CMS/Form/ShowForrmHeader/span_save'))

WebUI.click(findTestObject('CMS/Form/CheckboxLicenseAgreement/div_section_list'))

WebUI.click(findTestObject('CMS/Reviews/SectionReviewIdOnTheMain/div_top_menu'))

WebUI.click(findTestObject('CMS/Form/ShowForrmHeader/div_contacts'))

WebUI.click(findTestObject('CMS/Form/ShowForrmHeader/div_contacts'))

WebUI.click(findTestObject('Site/ReviewCheck/a_review_page_link'))

WebUI.switchToWindowIndex(1)

WebUI.verifyElementVisible(findTestObject('CMS/Form/TextRequiredFields/p_text_required_fieelds'))

