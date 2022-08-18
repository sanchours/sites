import React from 'react';
import { Tooltip } from 'antd';
import _ from 'lodash'
import Icon from "../../../../Icon/Index";
import * as sk from '../../../../../../services/_sk/api'

const handleClick = (props) => (e) => {
  // останавливаем дальнейшее всплытие события
  e.stopPropagation();

  const {record, mainContainerData, dispatch} = props;

  if ( record.prohib_del === "1" ){
    return null;
  }

  const rowText = record.title || record.name;

  const text = sk.dict('delRow').replace('{0}', rowText);

  sk.showModal(<div dangerouslySetInnerHTML={{ __html: sk.dict('delRowHeader') }} />, <div dangerouslySetInnerHTML={{ __html: text }} />, () => {

    dispatch({
      type: 'skGlobal/handleCustomButtonInParameters',
      payload: {
        dispatchCmd: 'FieldRemove',
        path: mainContainerData.get('path'),
        data: _.merge(
          {
            cmd: 'FieldRemove',
            data: {
              id: record.id
            }
          },
          mainContainerData.getIn(['params', 'serviceData']).toJS()
        )
      }
    });

  });

};

export default (props) => {

  const {record, configButton} = props;

  const {tooltip} = configButton;

  if (record.prohib_del === "1")
    return null;

  return (
    <Tooltip
      placement="bottomRight"
      title={tooltip}
    >
      <span>
        <Icon
          alias='icon-delete'
          addProps={{onClick: handleClick(props)}}
        />
      </span>
    </Tooltip>
  );
};
