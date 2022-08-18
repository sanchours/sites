import React from "react";
import TestTable from '../TestTable/Index';
import * as sk from "@/services/_sk/api";
import {Modal} from "antd";
import _ from "lodash";

/**
 * Клик по кнопкам левой панели
 * */
const handleClickOnButton = (selectedRows, moduleData, dispatch) => (configButton) => () => {

  const { state='' } = configButton;

  switch (state) {

    case 'copy_filelink':

      if (selectedRows.length === 0){
        sk.error(moduleData.getIn(['init','lang','chooseFile']));
        return false;
      }

      const items = selectedRows.map((item) =>
        <p key={item.key}>{item.webPathShort}</p>
      );

      Modal.info({
        title: moduleData.getIn(['init', 'lang', 'showFilesLink']),
        content: (
          <div>
             {items}
           </div>
         ),
         onOk(){

         },
       });

      break;

    case 'delete':


      // если не выбрано - выдать ошибку
      if ( !selectedRows.length ){
        sk.error(moduleData.getIn(['init','lang', 'fileBrowserNoSelection']));
        return false;
      }

      // задание текста для подтверждения
      let rowText;

      if ( selectedRows.length === 1 ){
        rowText = _.head(selectedRows).name || '';

        if ( rowText ){
          rowText = `"${rowText}"`;
        } else {
          rowText = moduleData.getIn(['init', 'lang', 'delRowNoName']);
        }

      } else {

        rowText = `${selectedRows.length.toString()} ${moduleData.getIn(['init', 'lang', 'delCntItems'])}`;

      }

      sk.showModal(sk.dict('delRowHeader'), <div dangerouslySetInnerHTML={{ __html: sk.dict('delRow').replace('{0}', rowText) }} />, () => {

        const delItems = selectedRows.map(item => item.name);

        const dataPack = moduleData.getIn(['params', 'serviceData']).toJS();

        const componentData = {
          cmd: 'delete',
          delItems
        };

        dispatch({
          type: 'skGlobal/deleteFileItems',
          payload: {
            path: moduleData.get('path'),
            data: {
              ...dataPack,
              ...componentData
            }
          }
        });

      });
      break;

    default:
      dispatch({
        type: 'skGlobal/filesAddForm',
        payload: {
          path: moduleData.get('path')
        }
      });

  }

};

export default (props) => {
  return (
    <TestTable
      {...props}
      handleClickOnButton={handleClickOnButton}
    />
  )
}
