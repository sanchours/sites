/**
 * @description Проверка отображения текста баннера на странице
 * @step Заходим в раздел Баннеры
 * @step Переходим в редактирование первого баннера в списке
 * @step Добавляем в него текст, ставим позицию левый столбец, ставим галочки Активность и На главной
 * @step Сохраняем Баннер
 * @step Переход на главную страницу публичной части
 * @step Проверка текста у первого баннера
 */
import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
import static com.kms.katalon.core.testcase.TestCaseFactory.findTestCase
import static com.kms.katalon.core.testdata.TestDataFactory.findTestData
import static com.kms.katalon.core.testobject.ObjectRepository.findTestObject
import com.kms.katalon.core.checkpoint.Checkpoint as Checkpoint
import com.kms.katalon.core.cucumber.keyword.CucumberBuiltinKeywords as CucumberKW
import com.kms.katalon.core.mobile.keyword.MobileBuiltInKeywords as Mobile
import com.kms.katalon.core.model.FailureHandling as FailureHandling
import com.kms.katalon.core.testcase.TestCase as TestCase
import com.kms.katalon.core.testdata.TestData as TestData
import com.kms.katalon.core.testobject.TestObject as TestObject
import com.kms.katalon.core.webservice.keyword.WSBuiltInKeywords as WS
import com.kms.katalon.core.webui.keyword.WebUiBuiltInKeywords as WebUI
import internal.GlobalVariable as GlobalVariable
import urlSite.BaseLink as BaseLink
import com.kms.katalon.core.exception.StepErrorException as StepErrorException

WebUI.click(findTestObject('Object Repository/CMS/News/CreateNewNews/div_top_menu'))

WebUI.click(findTestObject('Object Repository/CMS/News/CheckNewsOnMain/div_main'))

WebUI.click(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/div_banner_editor'))

WebUI.doubleClick(findTestObject('Object Repository/CMS/Banners/CheckDelBanners/div_name_first_banner'))

WebUI.setText(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/body_text_banner'), 'test text')

WebUI.click(findTestObject('CMS/Banners/CreateNewBanner/div_position_banner'))

WebUI.click(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/li_left_column_banner'))

if (WebUI.verifyElementChecked(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/input_activ_banner'), 1, FailureHandling.OPTIONAL) ==
	true) {
		WebUI.click(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/input_activ_banner'))
		}
	
if (WebUI.verifyElementChecked(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/input_on_main_banner'), 1, FailureHandling.OPTIONAL) ==
	true) {
		WebUI.click(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/input_on_main_banner'))
		}
		
WebUI.click(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/span_save_banner'))
	
WebUI.navigateToUrl(BaseLink.getUrlDef())

Text = WebUI.getText(findTestObject('Object Repository/CMS/Banners/CheckTextBanner/div_first_left_banner'))

if (Text != 'test text') {
	
	throw new StepErrorException("banner content is not as expected")
	
}