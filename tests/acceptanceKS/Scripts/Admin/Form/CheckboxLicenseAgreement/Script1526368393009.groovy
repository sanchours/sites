 /**
 * @description Галочка согласие с политикой конфиденциальности
 * @step Переход на детальную формы
 * @step Переход в параметр "Лицензионное соглашение"
 * @step Проверка установлена ли галочка вывода согласия с политикой конфиденциальности на форме
 * @step Если установлена:
 * @step	-Нажтие на кнопку схранить
 * @step	-Переход в раздел
 * @step	-Переход во вкладку редактор
 * @step	-Переход на страницу с формой
 * @step	-Проверка наличия согласия с политикой конфиденциальности
 * @step Иначе:
 * @step	-Установка галочки
 * @step	-Нажтие на кнопку схранить
 * @step	-Переход в раздел
 * @step	-Переход во вкладку редактор
 * @step	-Переход на страницу с формой
 * @step	-Проверка наличия согласия с политикой конфиденциальности
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

WebUI.click(findTestObject('CMS/Form/CheckboxLicenseAgreement/span_license_agreement'))

if (WebUI.verifyElementAttributeValue(findTestObject('CMS/Form/CheckboxLicenseAgreement/input_license_agreement_checkbox'), 
    'aria-checked', 'true', 1, FailureHandling.OPTIONAL) == true) {
    WebUI.click(findTestObject('CMS/Form/CheckboxLicenseAgreement/span_save'))

    WebUI.click(findTestObject('CMS/Form/CheckboxLicenseAgreement/div_section_list'))

    WebUI.click(findTestObject('CMS/Reviews/SectionReviewIdOnTheMain/div_top_menu'))

    WebUI.click(findTestObject('CMS/Form/ShowForrmHeader/div_contacts'))

    WebUI.click(findTestObject('CMS/Form/ShowForrmHeader/div_contacts'))

    WebUI.click(findTestObject('Site/ReviewCheck/a_review_page_link'))

    WebUI.switchToWindowIndex(1)

    WebUI.verifyElementVisible(findTestObject('CMS/Form/CheckboxLicenseAgreement/div_checkbox_license_agreement_on_page'))
} else {
    WebUI.click(findTestObject('CMS/Form/CheckboxLicenseAgreement/input_license_agreement_checkbox'))

    WebUI.click(findTestObject('CMS/Form/CheckboxLicenseAgreement/span_save'))

    WebUI.click(findTestObject('CMS/Form/CheckboxLicenseAgreement/div_section_list'))

    WebUI.click(findTestObject('CMS/Reviews/SectionReviewIdOnTheMain/div_top_menu'))

    WebUI.click(findTestObject('CMS/Form/ShowForrmHeader/div_contacts'))

    WebUI.click(findTestObject('CMS/Form/ShowForrmHeader/div_contacts'))

    WebUI.click(findTestObject('Site/ReviewCheck/a_review_page_link'))

    WebUI.switchToWindowIndex(1)

    WebUI.verifyElementVisible(findTestObject('CMS/Form/CheckboxLicenseAgreement/div_checkbox_license_agreement_on_page'))
}

