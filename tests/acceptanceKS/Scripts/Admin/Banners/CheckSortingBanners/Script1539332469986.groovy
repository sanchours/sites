/**
 * @description Проверка сортировки баннеров по позиции
 * @step Заходим в раздел Баннеры
 * @step Жмем на кнопку сортировки
 * @step Проверяем текст позиции у третьего баннера
 * @step Добавляем баннер, ставим позицию левый столбец
 * @step Проверяем текст позиции у третьего баннера
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
import com.kms.katalon.core.exception.StepErrorException as StepErrorException

WebUI.click(findTestObject('Object Repository/CMS/News/CreateNewNews/div_top_menu'))

WebUI.click(findTestObject('Object Repository/CMS/News/CheckNewsOnMain/div_main'))

WebUI.click(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/div_banner_editor'))

WebUI.click(findTestObject('Object Repository/CMS/Banners/CheckSortingBanners/span_sorting_banner'))

posthirdban = WebUI.getText(findTestObject('Object Repository/CMS/Banners/CheckSortingBanners/div_third_position_banner'))

if (posthirdban == 'Левая колонка') {

throw new StepErrorException("the initial number of banners in the left column does not match the test conditions")

}

WebUI.click(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/span_add_banner'))

WebUI.click(findTestObject('CMS/Banners/CreateNewBanner/div_position_banner'))

WebUI.click(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/li_left_column_banner'))

WebUI.click(findTestObject('Object Repository/CMS/Banners/CreateNewBanner/span_save_banner'))

posthirdban = WebUI.getText(findTestObject('Object Repository/CMS/Banners/CheckSortingBanners/div_third_position_banner'))

if (posthirdban != 'Левая колонка') {

throw new StepErrorException("sorting by position does not work correctly")

}