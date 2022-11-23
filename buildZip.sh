MODULE='miraklconnector'
ARCHIVE=$MODULE'.zip'

rm $ARCHIVE

cd ..

zip -r $ARCHIVE $MODULE -x $MODULE'/.git/*' -x $MODULE'/js/node_modules/*'

mv $ARCHIVE $MODULE