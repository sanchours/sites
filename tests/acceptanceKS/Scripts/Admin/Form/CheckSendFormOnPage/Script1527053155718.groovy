/**
 * @description Проверка отправки формы со страницы сайта
 * @step Переход в раздел
 * @step Переход в настройки форм
 * @step Подключение формы со всеми полями
 * @step Сохрениение изменений
 * @step Переход в таб "Редактор"
 * @step Переход на страницу по ссылке
 * @step Заполнение полей формы
 * @step Отправка формы
 * @step Проверка наличия результируюшей
 * 
 */

import static com.kms.katalon.core.checkpoint.CheckpointFactory.findCheckpoint
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
import org.openqa.selenium.WebDriver as WebDriver
import org.openqa.selenium.WebElement as WebElement
import com.kms.katalon.core.webui.driver.DriverFactory as DriverFactory
import org.junit.After as After
import org.openqa.selenium.By as By
import org.openqa.selenium.By.ByClassName as ByClassName
import org.openqa.selenium.By.ByXPath as ByXPath
import data.FormData as FormData


FormData DataForm = new FormData()

WebUI.click(findTestObject('CMS/TabSection/div_tab_form'))

WebUI.click(findTestObject('CMS/TabSection/select_form'))

WebUI.click(findTestObject('CMS/Form/CheckSendFormOnPage/li_form_with_all_fields'))

WebUI.click(findTestObject('Object Repository/CMS/TabSection/save_form_section'))

WebUI.click(findTestObject('CMS/TabSection/edit_section'))

WebUI.click(findTestObject('CMS/TabSection/section_link'))

WebUI.switchToWindowIndex(1)

WebDriver driver = DriverFactory.getWebDriver()

ArrayList<WebElement> listTypeField = driver.findElements(By.cssSelector('.form__col-1'))

int countListTypeField = listTypeField.size()

for (int numberField = 1; numberField <= (countListTypeField - 2); numberField++) {
    switch (numberField) {
        case 1:
            WebUI.click(findTestObject('CMS/Form/CheckSendFormOnPage/span_drop_down_list'))

            WebUI.click(findTestObject('CMS/Form/CheckSendFormOnPage/li_value_2_drop_down_list'))

            break
        case 2:
            WebUI.click(findTestObject('CMS/Form/CheckSendFormOnPage/label_checkbox'))

            break
        case 3:
            WebUI.click(findTestObject('CMS/Form/CheckSendFormOnPage/label_value_2_checkbox_group'))

            break
        case 4:
            WebUI.click(findTestObject('CMS/Form/CheckSendFormOnPage/label_value_2_radiogroup'))

            break
        case 5:
        case 6:
            WebUI.setText(findTestObject('CMS/Form/CheckSendFormOnPage/input_calendar'), DataForm.date)

            break
        case 7:
            WebUI.setText(findTestObject('CMS/Form/CheckSendFormOnPage/input_multiline_text_field'), DataForm.multilinetext)

            break
        case 8:
            WebUI.setText(findTestObject('CMS/Form/CheckSendFormOnPage/input_password'), DataForm.password)

            break
        case 9:
            WebUI.verifyElementVisible(findTestObject('CMS/Form/CheckSendFormOnPage/div_delimiter'))

            break
        case 10:
            WebUI.click(findTestObject('CMS/Form/CheckSendFormOnPage/div_value_2_rating'))

            break
        case 11:
            WebUI.setText(findTestObject('CMS/Form/CheckSendFormOnPage/input_text_field'), DataForm.text)

            break
    }
}

WebUI.click(findTestObject('CMS/Form/CheckSendFormOnPage/label_checkbox_private_policy'))

WebUI.click(findTestObject('CMS/Form/CheckSendFormOnPage/button_send'))

WebUI.verifyElementVisible(findTestObject('CMS/Form/CheckSendFormOnPage/div_resultant_page'))

