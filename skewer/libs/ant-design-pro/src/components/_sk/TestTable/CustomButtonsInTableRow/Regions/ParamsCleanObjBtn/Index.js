import React from 'react';
import { Tooltip } from 'antd';
import _ from 'lodash'
import Icon from "../../../../Icon/Index";
import * as sk from '../../../../../../services/_sk/api'

const handleClick = (props) => (e) => {
  // останавливаем дальнейшее всплытие события
  e.stopPropagation();

  const {record, mainContainerData, dispatch, configButton} = props;

  const {actionText} = configButton;

  // record = { id: 1, title: "ыва", alias: "ыва", default: "жэ\\fgh", defaultValueReplaced: true, regionId: "1", key: 1 }

  if ( record.defaultValueReplaced !== true ){
    return null;
  }

  sk.showModal(<div dangerouslySetInnerHTML={{ __html: sk.dict('allowDoHeader') }} />,<div dangerouslySetInnerHTML={{ __html: actionText }} />, () => {

    dispatch({
      type: 'skGlobal/handleCustomButtonInParameters',
      payload: {
        dispatchCmd: 'DeleteValueLabel',
        path: mainContainerData.get('path'),
        data: _.merge(
          {
            from: 'list',
            cmd: 'DeleteValueLabel',
            data: record
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

  if (!record.defaultValueReplaced){
    return null;
  }

  return (
    <Tooltip
      placement="bottomRight"
      title={tooltip}
    >
      <span>
        <Icon
          alias='icon-broom'
          addProps={{onClick: handleClick(props)}}
        />
      </span>
    </Tooltip>
  );
};
