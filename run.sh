
VOICE="$(php ./get_voice.php)"
if [ -n "${VOICE}" ]; then
    VOICE="-v ${VOICE}"
fi


while true
do
  OUTPUT="$(php ./lul.php $1 $2)"
  if [ -n "${OUTPUT}" ]; then
    echo "${OUTPUT}"
    echo "${OUTPUT}" | say ${VOICE};
  fi
  sleep 5
done
